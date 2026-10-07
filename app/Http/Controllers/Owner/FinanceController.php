<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FinanceController extends Controller
{
    public function index(Request $request)
    {
        $tab = in_array($request->tab, ['in', 'out', 'report']) ? $request->tab : 'in';
        $data = ['tab' => $tab];

        if ($tab === 'report') {
            $f = $this->filters($request);
            $rows = $request->has('show') ? $this->reportQuery($f)->get() : null;
            $data += ['f' => $f, 'rows' => $rows, 'sum' => $rows ? $this->sums($rows) : null];
        } else {
            $data['recent'] = Transaction::where('type', $tab)->latest('date')->latest('id')->limit(5)->get();
            $data['employees'] = $tab === 'out' ? Employee::all() : collect();
        }

        return view('owner.finance', $data);
    }

    public function store(Request $request)
    {
        $type = $request->input('type');
        $data = $request->validate([
            'type' => 'required|in:in,out',
            'date' => 'required|date',
            'category' => ['required', Rule::in($type === 'out' ? config('ochoa.expense_categories') : config('ochoa.income_categories'))],
            'amount' => 'required',
            'description' => 'required|string|max:255',
        ]);
        $data['amount'] = (int) preg_replace('/\D/', '', $data['amount']); // "Rp1.200.000" → 1200000
        abort_if($data['amount'] <= 0, 422, 'Nominal harus lebih dari 0.');

        Transaction::create($data);

        return redirect()->route('owner.finance', ['tab' => $type])->with('ok', 'Transaksi tersimpan.');
    }

    public function export(Request $request)
    {
        $f = $this->filters($request);
        $rows = $this->reportQuery($f)->get();
        $sum = $this->sums($rows);

        // Ubah ekstensi file ke .csv agar Excel membukanya tanpa peringatan format
        $filename = "OCHOA_Laporan_{$f['from']}_sd_{$f['to']}.csv";

        $headers = [
            "Content-type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename={$filename}",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function() use ($rows, $sum) {
            $file = fopen('php://output', 'w');
            
            // UTF-8 BOM agar aksen dan karakter khusus terbaca rapi di Excel
            fputs($file, "\xEF\xBB\xBF");
            
            // Header Kolom Utama
            fputcsv($file, ['TANGGAL', 'JENIS', 'KATEGORI', 'KETERANGAN', 'NOMINAL (Rp)']);

            // Data Transaksi
            foreach ($rows as $r) {
                fputcsv($file, [
                    // Menambahkan tanda petik tunggal di depan agar Excel membaca tanggal sebagai Teks biasa (tidak #####)
                    "'" . date('d/m/Y', strtotime($r->date)),
                    $r->type === 'in' ? 'Pemasukan' : 'Pengeluaran',
                    $r->category,
                    $r->description,
                    $r->amount
                ]);
            }

            // Baris Kosong Pemisah
            fputcsv($file, []);

            // Ringkasan Total & Laba Bersih di Bagian Bawah
            fputcsv($file, ['', '', '', 'TOTAL PEMASUKAN', $sum['in']]);
            fputcsv($file, ['', '', '', 'TOTAL PENGELUARAN', $sum['out']]);
            fputcsv($file, ['', '', '', 'TOTAL GAJI', $sum['gaji']]);
            fputcsv($file, ['', '', '', 'LABA BERSIH', $sum['laba']]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function filters(Request $r): array
    {
        return [
            'from' => $r->input('from', now()->startOfMonth()->toDateString()),
            'to' => $r->input('to', now()->endOfMonth()->toDateString()),
            'jenis' => $r->input('jenis', 'all'),
            'category' => $r->input('category', ''),
            'q' => $r->input('q', ''),
        ];
    }

    private function reportQuery(array $f)
    {
        return Transaction::query()
            ->whereBetween('date', [$f['from'], $f['to']])
            ->when($f['jenis'] === 'gaji', fn ($q) => $q->where('type', 'out')->where('category', 'Gaji'))
            ->when(in_array($f['jenis'], ['in', 'out']), fn ($q) => $q->where('type', $f['jenis']))
            ->when($f['category'], fn ($q, $c) => $q->where('category', $c))
            ->when($f['q'], fn ($q, $s) => $q->where(fn ($w) => $w->where('description', 'like', "%$s%")->orWhere('category', 'like', "%$s%")))
            ->orderBy('date')->orderBy('id');
    }

    private function sums($rows): array
    {
        $in = $rows->where('type', 'in')->sum('amount');
        $out = $rows->where('type', 'out')->sum('amount');
        $gaji = $rows->where('type', 'out')->where('category', 'Gaji')->sum('amount');

        return ['in' => $in, 'out' => $out, 'gaji' => $gaji, 'laba' => $in - $out];
    }
}