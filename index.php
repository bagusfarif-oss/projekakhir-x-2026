<?php
session_start();
date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['riwayat_transaksi'])) {
    $_SESSION['riwayat_transaksi'] = [];
}

class Pelanggan {
    private $nama;
    private $poinMember = 0;

    public function __construct($nama) {
        $this->nama = $nama;
    }

    public function getNama() {
         return $this->nama;
    }

    public function getPoin(){
         return $this->poinMember; 
    }

    public function tambahPoin($poin) {
         $this->poinMember += $poin; 
    }
}

class Voucher {
    public $kodeVoucher;
    private $diskon = 0;

    public function __construct($kodeVoucher) {
        $this->kodeVoucher = strtoupper(trim($kodeVoucher));
        if ($this->kodeVoucher === 'HEMAT10') {
            $this->diskon = 0.20; 
        } else if (in_array($this->kodeVoucher, ['V0UC3R', 'ANIVOUCER'])) {
            $this->diskon = 0.30; 
        } else if ($this->kodeVoucher === 'HEMATMAMAT') {
            $this->diskon = 0.50; 
        }
    }

    public function hitungDiskon() {
         return $this->diskon;
    }
}

class Menu {
    protected $namaMenu;
    protected $harga;

    public function getNamaMenu() {
         return $this->namaMenu;       
    }

    public function getHarga(){
         return $this->harga; 
    }
}

class Kopi extends Menu {
    private $ukuran;

    public function __construct($idMenu, $idUkuran) {
        if ($idMenu == 1) {
            $this->namaMenu = "Americano";
            $this->harga = 20000;
        } else if ($idMenu == 2) {
            $this->namaMenu = "Latte";
            $this->harga = 30000;
        }

        if ($idUkuran == 2) {
            $this->ukuran = "Medium";
            $this->harga -= 5000; 
        } else if ($idUkuran == 3) {
            $this->ukuran = "Large";
            $this->harga += 10000;
        } else {
            $this->ukuran = "Regular";
        }
    }

    public function getUkuran() {
         return $this->ukuran;
    }
}

class NonKopi extends Menu {
    private $rasa;

    public function __construct($idMenu) {
        if ($idMenu == 3) {
            $this->namaMenu = "Matcha Drink";
            $this->rasa = "Matcha";
            $this->harga = 24000;
        } else if ($idMenu == 4) {
            $this->namaMenu = "Chocolate Drink";
            $this->rasa = "Coklat";
            $this->harga = 22000;
        }
    }

    public function getRasa() {
         return $this->rasa; 
    }
}

class makanan extends Menu {
    public function __construct($idMenu) {
        if ($idMenu == 5) {
            $this->namaMenu = "Pizza";
            $this->harga = 100000;
        } else if ($idMenu == 6) { 
            $this->namaMenu = "Mie Ayam";
            $this->harga = 15000;
        }
    }
}

class Pesanan {
    private $pelanggan;
    private $voucher;
    private $subtotal = 0;

    public function __construct(Pelanggan $pelanggan, Voucher $voucher) {
        $this->pelanggan = $pelanggan;
        $this->voucher = $voucher;
    }

    public function tambahMenu(Menu $menu) {
        $this->subtotal += $menu->getHarga();
    }

    public function hitungSubtotal() { return $this->subtotal; }
    public function hitungDiskonVoucher() { return $this->subtotal * $this->voucher->hitungDiskon(); }

    public function hitungTotal() {
        $total = $this->subtotal - $this->hitungDiskonVoucher();
        $this->pelanggan->tambahPoin(floor($total / 10000));
        return $total;
    }
}
?>

<h2>Coffee System</h2>

<form method="post">
    <input type="text" name="Nama_Customer" placeholder="Nama Customer" required><br><br>
    
    <select id="jenis" name="Jenis" required>
        <option value="">Jenis Minuman & Makanan</option>
        <option value="1">Kopi</option>
        <option value="2">NonKopi</option>
        <option value="3">Makanan</option>
    </select><br><br>
    
    <select id="menu" name="Menu" required>
        <option value="">Pilih Menu</option>
    </select><br><br>
    
    <select name="Ukuran">
        <option value="1">Regular (Default)</option>
        <option value="2">Medium</option>
        <option value="3">Large</option>
    </select><br><br>

    <input type="text" name="Voucher" placeholder="Voucher (opsional)"><br><br>
    
    <button type="submit" name="btnSubmit">Order Now</button>
</form>
<br>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const jenisSelect = document.getElementById('jenis');
    const menuSelect = document.getElementById('menu');

    const opsiMenu = {
        '1': [{ value: '1', text: 'Americano' }, { value: '2', text: 'Latte' }],
        '2': [{ value: '3', text: 'Matcha' }, { value: '4', text: 'Chocolate' }],
        '3': [{ value: '5', text: 'Pizza' }, { value: '6', text: 'Mie Ayam' }]
    };

    jenisSelect.addEventListener('change', function() {
        menuSelect.innerHTML = '<option value="">Pilih Menu</option>';
        if (opsiMenu[this.value]) {
            opsiMenu[this.value].forEach(opt => {
                const option = new Option(opt.text, opt.value);
                menuSelect.add(option);
            });
        }
    });
});
</script>

<?php 
if (isset($_POST['btnSubmit'])) {
    $pelanggan = new Pelanggan($_POST['Nama_Customer']);
    $voucher = new Voucher($_POST['Voucher']);
    $detailPesanan = "";
    
    if ($_POST['Jenis'] == 1) {
        $ukuran = isset($_POST['Ukuran']) ? $_POST['Ukuran'] : 1;
        $menu = new Kopi($_POST['Menu'], $ukuran);
        $detailPesanan = $menu->getNamaMenu() . " (" . $menu->getUkuran() . ")";
    } else if ($_POST['Jenis'] == 2) { 
        $menu = new NonKopi($_POST['Menu']);
        $detailPesanan = $menu->getNamaMenu() . " (" . $menu->getRasa() . ")";
    } else {
        $menu = new makanan($_POST['Menu']);
        $detailPesanan = $menu->getNamaMenu(); 
    } 
    
    $pesanan = new Pesanan($pelanggan, $voucher);
    $pesanan->tambahMenu($menu);

    $subtotal = $pesanan->hitungSubtotal();
    $diskon = $pesanan->hitungDiskonVoucher();
    $totalBayar = $pesanan->hitungTotal();

    $_SESSION['riwayat_transaksi'][] = [
        'waktu' => date('H:i:s'),
        'menu'  => $detailPesanan,
        'harga' => $totalBayar
    ];

    echo "<strong>Order Summary</strong><br>";
    echo "Nama: " . htmlspecialchars($pelanggan->getNama()) . "<br>";
    echo "Menu: " . htmlspecialchars($detailPesanan) . "<br>";
    echo "Harga Awal: Rp " . number_format($subtotal, 0, ',', '.') . "<br>";
    echo "Total Bayar: Rp " . number_format($totalBayar, 0, ',', '.') . "<br>";

    if ($diskon > 0) {
        echo "Voucher: " . htmlspecialchars($voucher->kodeVoucher) . " (Diskon " . ($voucher->hitungDiskon() * 100) . "% / -Rp " . number_format($diskon, 0, ',', '.') . ")<br>";
    } else if (!empty($_POST['Voucher'])) {
        echo "Voucher: " . htmlspecialchars($_POST['Voucher']) . " (Tidak Valid)<br>";
    }

    echo "Poin: +" . $pelanggan->getPoin() . "<br><br>";
}

if (!empty($_SESSION['riwayat_transaksi'])) {
    echo "<strong>Riwayat Transaksi</strong><br>";
    foreach (array_reverse($_SESSION['riwayat_transaksi']) as $riwayat) {
        echo $riwayat['waktu'] . " - " . htmlspecialchars($riwayat['menu']) . " - Rp " . number_format($riwayat['harga'], 0, ',', '.') . "<br>";
    }
}
?>