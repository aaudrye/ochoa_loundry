@extends('layouts.app')
@section('title', 'Pesan Laundry')
@push('head')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<style>
    .step-content { display: none; }
    .step-content.active { display: block; }
    .stepper { display: flex; justify-content: space-between; margin: 20px 0 30px; position: relative; }
    .stepper::before { content: ''; position: absolute; top: 15px; left: 0; right: 0; height: 2px; background: var(--ln); z-index: 0; }
    .step-item { position: relative; z-index: 1; text-align: center; flex: 1; }
    .step-circle { width: 32px; height: 32px; border-radius: 50%; background: var(--ln); margin: 0 auto 8px; display: flex; align-items: center; justify-content: center; font-weight: 700; color: var(--mu); transition: all 0.3s; }
    .step-item.active .step-circle { background: var(--b); color: #fff; }
    .step-item.done .step-circle { background: var(--gr); color: #fff; }
    .step-label { font-size: 11px; color: var(--mu); font-weight: 600; }
    .step-item.active .step-label { color: var(--bd); font-weight: 800; }
    .time-slot { border: 1.5px solid var(--ln); border-radius: 12px; padding: 12px; cursor: pointer; transition: all 0.2s; }
    .time-slot:hover { border-color: var(--b); }
    .time-slot.selected { border: 2px solid var(--b); background: var(--soft); }
    .time-slot .time { font-weight: 800; color: var(--navy); font-size: 14px; }
    .time-slot .label { font-size: 11px; color: var(--mu); }
    .summary-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--ln); }
    .summary-row:last-child { border-bottom: 0; }
    .summary-row .lbl { color: var(--mu); font-size: 13px; }
    .summary-row .val { font-weight: 700; color: var(--navy); font-size: 13px; text-align: right; }
    .search-box { display: flex; gap: 8px; margin-bottom: 12px; }
    .search-box input { flex: 1; }
    .search-box button { white-space: nowrap; }
    #map { height: 250px; border-radius: 16px; border: 1px solid var(--ln); margin-top: 12px; }
    .map-info { font-size: 11px; color: var(--mu); margin-top: 8px; }
</style>
@endpush

@section('content')
<h1>Pesan Laundry</h1>
<p class="mu">Buat pesanan dalam beberapa langkah sederhana.</p>

{{-- Stepper Indicator --}}
<div class="stepper">
    <div class="step-item active" data-step="1">
        <div class="step-circle">1</div>
        <div class="step-label">Layanan</div>
    </div>
    <div class="step-item" data-step="2">
        <div class="step-circle">2</div>
        <div class="step-label">Detail</div>
    </div>
    <div class="step-item" data-step="3">
        <div class="step-circle">3</div>
        <div class="step-label">Pickup</div>
    </div>
    <div class="step-item" data-step="4">
        <div class="step-circle">4</div>
        <div class="step-label">Review</div>
    </div>
    <div class="step-item" data-step="5">
        <div class="step-circle">5</div>
        <div class="step-label">Selesai</div>
    </div>
</div>

<form method="POST" action="{{ route('customer.orders.store') }}" enctype="multipart/form-data" id="orderForm">@csrf

{{-- STEP 1: Layanan --}}
<div class="step-content active" id="step1">
    <div class="card">
        <h3>1. Pilih Layanan</h3>
        <div class="og" style="grid-template-columns:1fr 1fr">
            @foreach (config('ochoa.services') as $k => $s)
                <label class="op"><input type="radio" name="service" value="{{ $k }}" @checked(old('service', 'reguler') === $k)>
                    <b style="display:inline">{{ $s['icon'] }} {{ $s['label'] }}</b><span>@rp($s['price'])/kg · {{ $s['note'] }}</span></label>
            @endforeach
        </div>
    </div>
    <button type="button" class="btn w" onclick="nextStep(2)">Lanjut ke Detail →</button>
</div>

{{-- STEP 2: Detail Laundry --}}
<div class="step-content" id="step2">
    <div class="card">
        <h3>2. Detail Laundry</h3>
        <div class="lb">Warna Pakaian</div>
        <div class="og">
            @foreach (config('ochoa.colors') as [$ico, $name, $desc])
                <label class="op"><input type="radio" name="color" value="{{ $name }}" @checked(old('color', 'Campur Warna') === $name)><b style="display:inline">{{ $ico }} {{ $name }}</b><span>{{ $desc }}</span></label>
            @endforeach
        </div>
        <div class="lb">Pilihan Parfum</div>
        <div class="og">
            @foreach (config('ochoa.perfumes') as [$ico, $name, $desc])
                <label class="op"><input type="radio" name="perfume" value="{{ $name }}" @checked(old('perfume', 'Fresh Clean') === $name)><b style="display:inline">{{ $ico }} {{ $name }}</b><span>{{ $desc }}</span></label>
            @endforeach
        </div>
        <div class="f"><label>Kategori Pakaian</label>
            <select name="category">@foreach (config('ochoa.categories') as $c)<option @selected(old('category') === $c)>{{ $c }}</option>@endforeach</select></div>
        <div class="f"><label>Catatan untuk Laundry</label><textarea name="note" rows="3" placeholder="Contoh: pisahkan pakaian putih...">{{ old('note') }}</textarea></div>
        <div class="f"><label>Foto Laundry (opsional, maks. 4)</label><input type="file" name="photos[]" accept="image/*" multiple></div>
    </div>
    <div style="display:flex;gap:10px">
        <button type="button" class="btn l" onclick="prevStep(1)" style="flex:1">← Kembali</button>
        <button type="button" class="btn" onclick="nextStep(3)" style="flex:2">Lanjut ke Pickup →</button>
    </div>
</div>

{{-- STEP 3: Jadwal & Alamat Pickup --}}
<div class="step-content" id="step3">
    <div class="card">
        <h3>3. Jadwal & Alamat Pickup</h3>
        <p class="mu">Pilih kapan kurir datang mengambil laundry.</p>
        
        <div class="f">
            <label>Alamat Penjemputan</label>
            <div class="search-box">
                <input name="pickup_address" id="pickup_address" value="{{ old('pickup_address', auth()->user()->address) }}" placeholder="Ketik alamat lengkap..." required>
                <button type="button" class="btn s" onclick="searchAddress()">🔍 Cari</button>
            </div>
        </div>
        
        <div class="f"><label>Tanggal</label><input type="date" name="pickup_date" id="pickup_date" min="{{ today()->toDateString() }}" value="{{ old('pickup_date', today()->toDateString()) }}" required></div>
        
        <div class="lb">Jam Pickup</div>
        <div class="og" style="grid-template-columns:1fr 1fr">
            @foreach (config('ochoa.pickup_slots') as $t => $label)
                <div class="time-slot @checked(old('pickup_time', '10.00–12.00') === $t) ? 'selected' : ''" onclick="selectTime(this, '{{ $t }}')">
                    <div class="time">{{ $t }}</div>
                    <div class="label">{{ $label }}</div>
                </div>
            @endforeach
        </div>
        <input type="hidden" name="pickup_time" id="pickup_time" value="{{ old('pickup_time', '10.00–12.00') }}">
        
        <p class="mu" style="margin-top:16px">📍 Ketuk peta untuk menentukan titik jemput, atau cari alamat di atas.</p>
        <div id="map"></div>
        <div class="map-info"> Tip: Ketik alamat → klik "Cari" → peta akan otomatis menampilkan lokasi. Atau klik langsung di peta.</div>
        
        <input type="hidden" name="lat" id="lat" value="{{ old('lat', -6.3438) }}">
        <input type="hidden" name="lng" id="lng" value="{{ old('lng', 106.737) }}">
    </div>
    
    <div style="display:flex;gap:10px">
        <button type="button" class="btn l" onclick="prevStep(2)" style="flex:1">← Kembali</button>
        <button type="button" class="btn" onclick="nextStep(4)" style="flex:2">Review Pesanan →</button>
    </div>
</div>

{{-- STEP 4: Review Pesanan --}}
<div class="step-content" id="step4">
    <div class="card">
        <h3>Ringkasan Pesanan</h3>
        <div style="margin:16px 0">
            <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--ln)">
                <span style="color:var(--mu);font-size:13px">Layanan</span>
                <span style="font-weight:700;color:var(--navy);font-size:13px;text-align:right" id="summary-service">-</span>
            </div>
            <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--ln)">
                <span style="color:var(--mu);font-size:13px">Warna</span>
                <span style="font-weight:700;color:var(--navy);font-size:13px;text-align:right" id="summary-color">-</span>
            </div>
            <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--ln)">
                <span style="color:var(--mu);font-size:13px">Parfum</span>
                <span style="font-weight:700;color:var(--navy);font-size:13px;text-align:right" id="summary-perfume">-</span>
            </div>
            <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--ln)">
                <span style="color:var(--mu);font-size:13px">Kategori</span>
                <span style="font-weight:700;color:var(--navy);font-size:13px;text-align:right" id="summary-category">-</span>
            </div>
            <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--ln)">
                <span style="color:var(--mu);font-size:13px">Pickup</span>
                <span style="font-weight:700;color:var(--navy);font-size:13px;text-align:right" id="summary-pickup">-</span>
            </div>
            <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--ln)">
                <span style="color:var(--mu);font-size:13px">Alamat</span>
                <span style="font-weight:700;color:var(--navy);font-size:13px;text-align:right;max-width:60%" id="summary-address">-</span>
            </div>
            <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--ln)">
                <span style="color:var(--mu);font-size:13px">Catatan</span>
                <span style="font-weight:700;color:var(--navy);font-size:13px;text-align:right" id="summary-note">-</span>
            </div>
            <div style="display:flex;justify-content:space-between;padding:8px 0">
                <span style="color:var(--mu);font-size:13px">Berat & Total</span>
                <span style="font-weight:700;color:var(--navy);font-size:13px;text-align:right">Dihitung kurir setelah penimbangan</span>
            </div>
        </div>
    </div>
    <div class="card" style="background:var(--soft);padding:12px 16px">
         Kurir menimbang dengan timbangan digital di lokasi, lalu tagihan muncul di aplikasi. Pastikan alamat & jadwal pickup sudah benar.
    </div>
    <div style="display:flex;gap:10px">
        <button type="button" class="btn l" onclick="prevStep(3)" style="flex:1">← Kembali</button>
        <button type="submit" class="btn" style="flex:2">✓ Buat Pesanan</button>
    </div>
</div>

</form>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
let currentStep = 1;
let map, pin;

function updateStepper() {
    document.querySelectorAll('.step-item').forEach((item, idx) => {
        const stepNum = idx + 1;
        item.classList.remove('active', 'done');
        if (stepNum === currentStep) item.classList.add('active');
        else if (stepNum < currentStep) item.classList.add('done');
    });
    document.querySelectorAll('.step-content').forEach((content, idx) => {
        content.classList.toggle('active', idx + 1 === currentStep);
    });
    window.scrollTo({ top: 0, behavior: 'smooth' });
    
    // Inisialisasi peta saat step 3 aktif
    if (currentStep === 3 && !map) {
        initMap();
    }
}

function nextStep(step) {
    // Validasi sederhana sebelum lanjut
    if (currentStep === 1) {
        if (!document.querySelector('input[name="service"]:checked')) {
            alert('Pilih layanan terlebih dahulu');
            return;
        }
    }
    if (currentStep === 2) {
        if (!document.querySelector('input[name="color"]:checked')) {
            alert('Pilih warna pakaian');
            return;
        }
        if (!document.querySelector('input[name="perfume"]:checked')) {
            alert('Pilih parfum');
            return;
        }
    }
    if (currentStep === 3) {
        const address = document.getElementById('pickup_address').value;
        const date = document.getElementById('pickup_date').value;
        const time = document.getElementById('pickup_time').value;
        if (!address || !date || !time) {
            alert('Lengkapi alamat, tanggal, dan jam pickup');
            return;
        }
    }
    
    if (step === 4) updateSummary();
    currentStep = step;
    updateStepper();
}

function prevStep(step) {
    currentStep = step;
    updateStepper();
}

function selectTime(el, time) {
    document.querySelectorAll('.time-slot').forEach(s => s.classList.remove('selected'));
    el.classList.add('selected');
    document.getElementById('pickup_time').value = time;
}

// Fungsi pencarian alamat dengan geocoding
async function searchAddress() {
    const address = document.getElementById('pickup_address').value.trim();
    if (!address) {
        alert('Masukkan alamat terlebih dahulu');
        return;
    }
    
    const searchBtn = event.target;
    searchBtn.textContent = '⏳ Mencari...';
    searchBtn.disabled = true;
    
    try {
        // Gunakan Nominatim API (OpenStreetMap) untuk geocoding
        const response = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}&limit=1`, {
            headers: {
                'User-Agent': 'OCHOA-Laundry-App/1.0'
            }
        });
        const data = await response.json();
        
        if (data && data.length > 0) {
            const lat = parseFloat(data[0].lat);
            const lng = parseFloat(data[0].lon);
            
            // Update koordinat
            document.getElementById('lat').value = lat;
            document.getElementById('lng').value = lng;
            
            // Update peta
            if (map && pin) {
                pin.setLatLng([lat, lng]);
                map.setView([lat, lng], 16);
            }
            
            // Tampilkan alamat lengkap dari hasil pencarian
            if (data[0].display_name) {
                document.getElementById('pickup_address').value = data[0].display_name;
            }
        } else {
            alert('Alamat tidak ditemukan. Coba gunakan alamat yang lebih lengkap.');
        }
    } catch (error) {
        console.error('Geocoding error:', error);
        alert('Gagal mencari alamat. Pastikan koneksi internet aktif.');
    } finally {
        searchBtn.textContent = '🔍 Cari';
        searchBtn.disabled = false;
    }
}

// Fungsi reverse geocoding (klik di peta → dapat alamat)
async function reverseGeocode(lat, lng) {
    try {
        const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`, {
            headers: {
                'User-Agent': 'OCHOA-Laundry-App/1.0'
            }
        });
        const data = await response.json();
        
        if (data && data.display_name) {
            document.getElementById('pickup_address').value = data.display_name;
        }
    } catch (error) {
        console.error('Reverse geocoding error:', error);
    }
}

function initMap() {
    const lat = parseFloat(document.getElementById('lat').value);
    const lng = parseFloat(document.getElementById('lng').value);
    
    map = L.map('map').setView([lat, lng], 15);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap',
        maxZoom: 19
    }).addTo(map);
    
    pin = L.marker([lat, lng], { draggable: true }).addTo(map);
    
    // Saat pin di-drag, update koordinat dan alamat
    pin.on('dragend', async function(e) {
        const pos = e.target.getLatLng();
        document.getElementById('lat').value = pos.lat.toFixed(7);
        document.getElementById('lng').value = pos.lng.toFixed(7);
        
        // Reverse geocoding untuk dapat alamat
        await reverseGeocode(pos.lat, pos.lng);
    });
    
    // Saat peta diklik, pindahkan pin dan update
    map.on('click', async function(e) {
        pin.setLatLng(e.latlng);
        document.getElementById('lat').value = e.latlng.lat.toFixed(7);
        document.getElementById('lng').value = e.latlng.lng.toFixed(7);
        
        // Reverse geocoding untuk dapat alamat
        await reverseGeocode(e.latlng.lat, e.latlng.lng);
    });
}

function updateSummary() {
    const service = document.querySelector('input[name="service"]:checked');
    const color = document.querySelector('input[name="color"]:checked');
    const perfume = document.querySelector('input[name="perfume"]:checked');
    const category = document.querySelector('select[name="category"]').value;
    const date = document.getElementById('pickup_date').value;
    const time = document.getElementById('pickup_time').value;
    const address = document.getElementById('pickup_address').value;
    const note = document.querySelector('textarea[name="note"]').value;
    
    if (service) document.getElementById('summary-service').textContent = service.parentElement.querySelector('b').textContent.trim();
    if (color) document.getElementById('summary-color').textContent = color.value;
    if (perfume) document.getElementById('summary-perfume').textContent = perfume.value;
    document.getElementById('summary-category').textContent = category;
    document.getElementById('summary-pickup').textContent = `${date} · ${time}`;
    document.getElementById('summary-address').textContent = address || '-';
    document.getElementById('summary-note').textContent = note || 'Tidak ada catatan';
}

// Inisialisasi time slot yang sudah dipilih
document.querySelectorAll('.time-slot').forEach(slot => {
    if (slot.classList.contains('selected')) {
        const time = slot.querySelector('.time').textContent;
        document.getElementById('pickup_time').value = time;
    }
});

// Enter key di input alamat → trigger search
document.getElementById('pickup_address').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        searchAddress();
    }
});
</script>
@endpush