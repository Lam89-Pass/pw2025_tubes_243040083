<?php
// KONEKSI DATABASE
if (!isset($conn) || !$conn) {
    $host = 'localhost';
    $user = 'root';
    $pass = '';
    $db   = 'bengkelinaja';
    $conn = mysqli_connect($host, $user, $pass, $db);

    if (!$conn) {
        error_log("Koneksi database GAGAL di functions.php: " . mysqli_connect_error());
        // Hentikan eksekusi jika koneksi gagal.
        die("Koneksi database gagal. Silakan periksa konfigurasi dan pastikan server database berjalan.");
    }
}

// FUNGSI DASAR UTILITY
function query($query_string)
{
    global $conn;
    if (!$conn) return false;
    $result = mysqli_query($conn, $query_string);
    if (!$result) {
        error_log("Query Error: " . mysqli_error($conn) . " | Query: " . $query_string);
        return false;
    }
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}

// FUNGSI OTENTIKASI & OTORISASI
function registrasi($data)
{
    global $conn;
    // Ambil input mentah untuk divalidasi
    $username_input = stripslashes($data["username"]);

    $email = strtolower(stripslashes($data["email"]));
    $password = $data["password"];
    $confirmPassword = $data["confirmPassword"];

    // Validasi dasar
    if (empty($username_input) || empty($email) || empty($password)) {
        return ['success' => false, 'message' => 'Semua kolom wajib diisi.'];
    }

    // Validasi username baru pada input asli 
    if (preg_match('/\s/', $username_input)) {
        return ['success' => false, 'message' => 'Username tidak boleh mengandung spasi.'];
    }
    // Regex ini akan gagal jika ada karakter selain huruf kecil [a-z] dan angka [0-9]
    if (!preg_match('/^[a-z0-9]+$/', $username_input)) {
        return ['success' => false, 'message' => 'Username hanya boleh berisi huruf kecil dan angka.'];
    }

    // Setelah validasi lolos, kita bisa menggunakan username tersebut
    $username = $username_input;

    // Cek username sudah ada atau belum
    $stmt_check = mysqli_prepare($conn, "SELECT username FROM users WHERE username = ?");
    mysqli_stmt_bind_param($stmt_check, "s", $username);
    mysqli_stmt_execute($stmt_check);
    mysqli_stmt_store_result($stmt_check);
    if (mysqli_stmt_num_rows($stmt_check) > 0) {
        mysqli_stmt_close($stmt_check);
        return ['success' => false, 'message' => 'Username sudah terdaftar!'];
    }
    mysqli_stmt_close($stmt_check);

    if ($password !== $confirmPassword) {
        return ['success' => false, 'message' => 'Konfirmasi password tidak sesuai!'];
    }
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Query INSERT menggunakan username yang sudah divalidasi
    $stmt_insert = mysqli_prepare($conn, "INSERT INTO users (username, email, password, created_at) VALUES (?, ?, ?, NOW())");
    mysqli_stmt_bind_param($stmt_insert, "sss", $username, $email, $hashed_password);
    if (mysqli_stmt_execute($stmt_insert)) {
        return ['success' => true, 'message' => 'Registrasi berhasil! Silakan login.'];
    }
    return ['success' => false, 'message' => 'Registrasi gagal!'];
}

function is_admin()
{
    return (isset($_SESSION['login']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin');
}

function protect_admin_page()
{
    // Cek jika sesi login tidak ada atau peran bukan admin dan bukan staff
    if (!isset($_SESSION['login']) || !in_array($_SESSION['user_role'], ['admin', 'staff'])) {
        $_SESSION['error_message'] = "Anda tidak memiliki hak akses ke halaman ini.";
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];
        $path_to_login = $protocol . "://" . $host . "/pw2025_tubes_243040083/login.php";
        header("Location: " . $path_to_login);
        exit;
    }
}
function can_access_panel()
{
    return (isset($_SESSION['login']) && isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'staff']));
}

// melindungi halaman hanya untuk admin
function protect_super_admin_page()
{
    if (!is_admin()) {
        // Jika bukan admin, tendang ke dashboard admin (halaman yang boleh diakses staff)
        header("Location: index.php");
        exit;
    }
}

// FUNGSI MANAJEMEN GAMBAR (UPLOAD)

// Fungsi untuk mengupload gambar produk atau foto profil
function upload_gambar_produk($file_input, $target_subfolder = 'img_produk')
{
    $nama_file = $file_input['name'];
    $ukuran_file = $file_input['size'];
    $error_file = $file_input['error'];
    $tmp_name = $file_input['tmp_name'];
    if ($error_file === UPLOAD_ERR_NO_FILE) return ['success' => true, 'filename' => null];
    if ($error_file !== UPLOAD_ERR_OK) return ['success' => false, 'message' => 'Error upload: ' . $error_file];
    $ekstensi_gambar_valid = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $ekstensi_gambar = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));
    if (!in_array($ekstensi_gambar, $ekstensi_gambar_valid)) return ['success' => false, 'message' => 'Format file gambar tidak valid!'];
    if ($ukuran_file > 2000000) return ['success' => false, 'message' => 'Ukuran file gambar terlalu besar! (Maks 2MB)'];
    $prefix = ($target_subfolder === 'foto_profil') ? 'user_' : 'prod_';
    $nama_file_unik = $prefix . uniqid() . '_' . time() . '.' . $ekstensi_gambar;
    $target_dir_absolute = __DIR__ . '/img/' . $target_subfolder . '/';
    if (!is_dir($target_dir_absolute)) mkdir($target_dir_absolute, 0775, true);
    $lokasi_target_file_absolut = $target_dir_absolute . $nama_file_unik;
    if (move_uploaded_file($tmp_name, $lokasi_target_file_absolut)) return ['success' => true, 'filename' => $nama_file_unik];
    return ['success' => false, 'message' => 'Gagal memindahkan file gambar.'];
}

// FUNGSI MANAJEMEN PRODUK
function get_produk_by_id($id_produk)
{
    global $conn;
    $id_produk = (int)$id_produk;
    $stmt = mysqli_prepare($conn, "SELECT p.*, c.nama_kategori FROM product p LEFT JOIN categories c ON p.category_id = c.id_kategori WHERE p.id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id_produk);
    mysqli_stmt_execute($stmt);
    return mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}


// Fungsi Tambah Produk
function tambah_produk($data, $file)
{
    global $conn;
    $nama = htmlspecialchars(trim($data['nama_produk']));
    $category_id = (int)$data['category_id'];
    $harga = (int)$data['harga'];
    $original_price = !empty($data['original_price']) ? (int)$data['original_price'] : null;
    $stok = (int)$data['stok'];
    $deskripsi = htmlspecialchars(trim($data['deskripsi']));
    $merek_ids = $data['merek'] ?? [];
    $gambar = '';
    if (empty($nama) || empty($category_id) || $harga <= 0 || $stok < 0) {
        return ['success' => false, 'message' => 'Form wajib diisi dengan benar.'];
    }
    if (isset($file['gambar_produk']) && $file['gambar_produk']['error'] == UPLOAD_ERR_OK) {
        $upload_result = upload_gambar_produk($file['gambar_produk'], 'img_produk');
        if ($upload_result['success'] && !empty($upload_result['filename'])) {
            $gambar = $upload_result['filename'];
        } elseif (!$upload_result['success']) {
            return ['success' => false, 'message' => $upload_result['message']];
        }
    }
    $stmt = mysqli_prepare($conn, "INSERT INTO product (name, category_id, price, original_price, stock, image, deskripsi, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
    mysqli_stmt_bind_param($stmt, "siiisss", $nama, $category_id, $harga, $original_price, $stok, $gambar, $deskripsi);
    if (mysqli_stmt_execute($stmt)) {
        $new_product_id = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt);
        update_produk_merek($new_product_id, $merek_ids);
        return ['success' => true, 'message' => 'Produk berhasil ditambahkan!'];
    } else {
        if (!empty($gambar) && file_exists("img/img_produk/" . $gambar)) unlink("img/img_produk/" . $gambar);
        return ['success' => false, 'message' => 'Gagal menambahkan produk.'];
    }
}


// Fungsi Update Produk
function update_produk($data, $file, $id_produk)
{
    global $conn;
    $id_produk = (int)$id_produk;
    $nama = htmlspecialchars(trim($data['nama_produk']));
    $category_id = (int)$data['category_id'];
    $harga = (int)$data['harga'];
    $original_price = !empty($data['original_price']) ? (int)$data['original_price'] : null;
    $stok = (int)$data['stok'];
    $deskripsi = htmlspecialchars(trim($data['deskripsi']));
    $merek_ids = $data['merek'] ?? [];
    $gambar_lama = $data['gambar_lama'] ?? '';
    $gambar_baru = $gambar_lama;
    $is_photo_updated = false;
    if (isset($file['gambar_produk']) && $file['gambar_produk']['error'] == UPLOAD_ERR_OK) {
        $upload_result = upload_gambar_produk($file['gambar_produk'], 'img_produk');
        if ($upload_result['success'] && !empty($upload_result['filename'])) {
            $gambar_baru = $upload_result['filename'];
            $is_photo_updated = true;
            if (!empty($gambar_lama) && $gambar_baru !== $gambar_lama && file_exists("img/img_produk/" . $gambar_lama)) {
                unlink("img/img_produk/" . $gambar_lama);
            }
        } elseif (!$upload_result['success'] && $upload_result['message'] !== 'Tidak ada file gambar baru yang diupload.') {
            return ['success' => false, 'message' => $upload_result['message']];
        }
    }
    $stmt = mysqli_prepare($conn, "UPDATE product SET name=?, category_id=?, price=?, original_price=?, stock=?, image=?, deskripsi=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, "siiisssi", $nama, $category_id, $harga, $original_price, $stok, $gambar_baru, $deskripsi, $id_produk);
    $is_updated = false;
    if (mysqli_stmt_execute($stmt)) {
        if (mysqli_stmt_affected_rows($stmt) > 0) $is_updated = true;
        mysqli_stmt_close($stmt);
    } else {
        return ['success' => false, 'message' => 'Gagal update produk.'];
    }
    update_produk_merek($id_produk, $merek_ids);
    if ($is_updated || $is_photo_updated) {
        return ['success' => true, 'message' => 'Produk berhasil diperbarui!'];
    }
    return ['success' => false, 'message' => 'Tidak ada perubahan data.'];
}

function hapus_produk($id_produk)
{
    global $conn;
    $produk = get_produk_by_id($id_produk);
    if (!$produk) return ['success' => false, 'message' => 'Produk tidak ditemukan.'];
    $gambar_produk = $produk['image'];
    $stmt = mysqli_prepare($conn, "DELETE FROM product WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id_produk);
    if (mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) > 0) {
        if (!empty($gambar_produk) && file_exists("img/img_produk/" . $gambar_produk)) unlink("img/img_produk/" . $gambar_produk);
        return ['success' => true, 'message' => 'Produk berhasil dihapus!'];
    }
    return ['success' => false, 'message' => 'Gagal menghapus produk.'];
}

// FUNGSI MANAJEMEN KATEGORI & MEREK
function get_all_categories()
{
    return query("SELECT * FROM categories ORDER BY nama_kategori ASC");
}
function tambah_kategori($nama_kategori)
{
    global $conn;
    $nama_kategori = htmlspecialchars(trim($nama_kategori));
    if (empty($nama_kategori)) return ['success' => false, 'message' => 'Nama kategori tidak boleh kosong.'];
    $stmt_check = mysqli_prepare($conn, "SELECT id_kategori FROM categories WHERE nama_kategori = ?");
    mysqli_stmt_bind_param($stmt_check, "s", $nama_kategori);
    mysqli_stmt_execute($stmt_check);
    if (mysqli_stmt_get_result($stmt_check)->num_rows > 0) return ['success' => false, 'message' => 'Kategori sudah ada.'];
    $stmt_insert = mysqli_prepare($conn, "INSERT INTO categories (nama_kategori) VALUES (?)");
    mysqli_stmt_bind_param($stmt_insert, "s", $nama_kategori);
    if (mysqli_stmt_execute($stmt_insert)) return ['success' => true, 'message' => 'Kategori berhasil ditambahkan!'];
    return ['success' => false, 'message' => 'Gagal menambahkan kategori.'];
}
function get_kategori_by_id($id_kategori)
{
    global $conn;
    $stmt = mysqli_prepare($conn, "SELECT * FROM categories WHERE id_kategori = ?");
    mysqli_stmt_bind_param($stmt, "i", $id_kategori);
    mysqli_stmt_execute($stmt);
    return mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}
function update_kategori($id_kategori, $nama_kategori_baru)
{
    global $conn;
    $nama_kategori_baru = htmlspecialchars(trim($nama_kategori_baru));
    if (empty($nama_kategori_baru)) return ['success' => false, 'message' => 'Nama kategori tidak boleh kosong.'];
    $stmt_update = mysqli_prepare($conn, "UPDATE categories SET nama_kategori = ? WHERE id_kategori = ?");
    mysqli_stmt_bind_param($stmt_update, "si", $nama_kategori_baru, $id_kategori);
    if (mysqli_stmt_execute($stmt_update) && mysqli_stmt_affected_rows($stmt_update) > 0) return ['success' => true, 'message' => 'Kategori berhasil diperbarui!'];
    return ['success' => false, 'message' => 'Tidak ada perubahan data.'];
}
function hapus_kategori($id_kategori)
{
    global $conn;
    $stmt_delete = mysqli_prepare($conn, "DELETE FROM categories WHERE id_kategori = ?");
    mysqli_stmt_bind_param($stmt_delete, "i", $id_kategori);
    if (mysqli_stmt_execute($stmt_delete) && mysqli_stmt_affected_rows($stmt_delete) > 0) return ['success' => true, 'message' => 'Kategori berhasil dihapus!'];
    return ['success' => false, 'message' => 'Gagal menghapus kategori (mungkin masih terkait produk).'];
}
function get_all_merek()
{
    return query("SELECT * FROM merek ORDER BY nama_merek ASC");
}
function get_merek_for_product($product_id)
{
    global $conn;
    $sql = "SELECT m.id_merek, m.nama_merek FROM merek m JOIN produk_merek pm ON m.id_merek = pm.merek_id WHERE pm.product_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $product_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $merek_list = [];
    while ($row = mysqli_fetch_assoc($result)) $merek_list[$row['id_merek']] = $row['nama_merek'];
    mysqli_stmt_close($stmt);
    return $merek_list;
}
function update_produk_merek($product_id, $merek_ids = [])
{
    global $conn;
    $stmt_delete = mysqli_prepare($conn, "DELETE FROM produk_merek WHERE product_id = ?");
    mysqli_stmt_bind_param($stmt_delete, "i", $product_id);
    mysqli_stmt_execute($stmt_delete);
    mysqli_stmt_close($stmt_delete);
    if (!empty($merek_ids) && is_array($merek_ids)) {
        $sql_insert = "INSERT INTO produk_merek (product_id, merek_id) VALUES ";
        $placeholders = [];
        $params = [];
        $types = "";
        foreach ($merek_ids as $merek_id) {
            $placeholders[] = "(?, ?)";
            $params[] = $product_id;
            $params[] = (int)$merek_id;
            $types .= "ii";
        }
        $sql_insert .= implode(", ", $placeholders);
        $stmt_insert = mysqli_prepare($conn, $sql_insert);
        mysqli_stmt_bind_param($stmt_insert, $types, ...$params);
        return mysqli_stmt_execute($stmt_insert);
    }
    return true;
}

// FUNGSI MANAJEMEN PENGGUNA & PROFIL
function get_all_users()
{
    return query("SELECT id, username, email, role, nama_lengkap, no_hp, alamat_lengkap, foto_profil, created_at FROM users ORDER BY created_at DESC");
}
function get_user_by_id($id_user)
{
    global $conn;
    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id_user);
    mysqli_stmt_execute($stmt);
    return mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

function update_user_role($id_user, $new_role)
{
    global $conn;
    $id_user = (int)$id_user;
    // Tambahkan 'staff' ke dalam daftar peran yang diizinkan
    $allowed_roles = ['user', 'staff', 'admin'];
    if (!in_array($new_role, $allowed_roles)) {
        return ['success' => false, 'message' => 'Peran tidak valid.'];
    }

    // Logika untuk mencegah admin terakhir diubah perannya tetap sama
    if ($new_role !== 'admin') {
        $user_to_change = get_user_by_id($id_user);
        if ($user_to_change && $user_to_change['role'] === 'admin') {
            $result_admin = query("SELECT COUNT(*) as total_admins FROM users WHERE role = 'admin'");
            if ($result_admin && $result_admin[0]['total_admins'] <= 1) {
                return ['success' => false, 'message' => 'Tidak dapat mengubah peran admin terakhir.'];
            }
        }
    }

    $stmt_update = mysqli_prepare($conn, "UPDATE users SET role = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt_update, "si", $new_role, $id_user);
    if (mysqli_stmt_execute($stmt_update) && mysqli_stmt_affected_rows($stmt_update) > 0) {
        return ['success' => true, 'message' => 'Peran pengguna berhasil diperbarui!'];
    }
    return ['success' => false, 'message' => 'Tidak ada perubahan atau terjadi kesalahan.'];
}
function hapus_pengguna($id_user_to_delete, $current_admin_id)
{
    global $conn;
    if ($id_user_to_delete === $current_admin_id) return ['success' => false, 'message' => 'Anda tidak dapat menghapus akun Anda sendiri.'];
    $user_to_delete = get_user_by_id($id_user_to_delete);
    if (!$user_to_delete) return ['success' => false, 'message' => 'Pengguna tidak ditemukan.'];
    if ($user_to_delete['role'] === 'admin') {
        $admin_count = query("SELECT COUNT(*) as total FROM users WHERE role = 'admin'")[0]['total'];
        if ($admin_count <= 1) return ['success' => false, 'message' => 'Tidak dapat menghapus admin terakhir.'];
    }
    $foto_profil_to_delete = $user_to_delete['foto_profil'];
    $stmt_delete = mysqli_prepare($conn, "DELETE FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt_delete, "i", $id_user_to_delete);
    if (mysqli_stmt_execute($stmt_delete) && mysqli_stmt_affected_rows($stmt_delete) > 0) {
        if (!empty($foto_profil_to_delete) && file_exists("img/foto_profil/" . $foto_profil_to_delete)) unlink("img/foto_profil/" . $foto_profil_to_delete);
        return ['success' => true, 'message' => 'Pengguna berhasil dihapus!'];
    }
    return ['success' => false, 'message' => 'Gagal menghapus pengguna.'];
}
function update_user_profile($user_id, $data, $file)
{
    global $conn;
    $nama_lengkap = htmlspecialchars(trim($data['nama_lengkap']));
    $no_hp = htmlspecialchars(trim($data['no_hp']));
    $alamat_lengkap = htmlspecialchars(trim($data['alamat_lengkap']));
    $jenis_kelamin = isset($data['jenis_kelamin']) && in_array($data['jenis_kelamin'], ['Laki-laki', 'Perempuan']) ? $data['jenis_kelamin'] : null;
    $tanggal_lahir = !empty($data['tanggal_lahir']) ? htmlspecialchars($data['tanggal_lahir']) : null;
    $foto_profil_lama = $data['foto_profil_lama'] ?? '';
    $foto_profil_baru = $foto_profil_lama;
    $is_photo_updated = false;
    if (isset($file['foto_profil_baru']) && $file['foto_profil_baru']['error'] == UPLOAD_ERR_OK) {
        $upload_result = upload_gambar_produk($file['foto_profil_baru'], 'foto_profil');
        if ($upload_result['success'] && !empty($upload_result['filename'])) {
            $foto_profil_baru = $upload_result['filename'];
            $is_photo_updated = true;
            if (!empty($foto_profil_lama) && file_exists("img/foto_profil/" . $foto_profil_lama)) {
                unlink("img/foto_profil/" . $foto_profil_lama);
            }
        } elseif (!$upload_result['success'] && $upload_result['message'] !== 'Tidak ada file gambar baru yang diupload.') {
            return ['success' => false, 'message' => $upload_result['message']];
        }
    }
    $stmt = mysqli_prepare($conn, "UPDATE users SET nama_lengkap = ?, no_hp = ?, alamat_lengkap = ?, foto_profil = ?, jenis_kelamin = ?, tanggal_lahir = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "ssssssi", $nama_lengkap, $no_hp, $alamat_lengkap, $foto_profil_baru, $jenis_kelamin, $tanggal_lahir, $user_id);
    if (mysqli_stmt_execute($stmt)) {
        if (mysqli_stmt_affected_rows($stmt) > 0 || $is_photo_updated) {
            return ['success' => true, 'message' => 'Profil berhasil diperbarui!'];
        }
        return ['success' => false, 'message' => 'Tidak ada perubahan data pada profil.'];
    }
    return ['success' => false, 'message' => 'Gagal memperbarui profil di database.'];
}
function ubah_password_user($user_id, $password_saat_ini, $password_baru, $konfirmasi_password_baru)
{
    global $conn;
    if (empty($password_saat_ini) || empty($password_baru) || empty($konfirmasi_password_baru)) return ['success' => false, 'message' => 'Semua field wajib diisi.'];
    if (strlen($password_baru) < 6) return ['success' => false, 'message' => 'Password baru minimal 6 karakter.'];
    if ($password_baru !== $konfirmasi_password_baru) return ['success' => false, 'message' => 'Konfirmasi password baru tidak cocok.'];
    $user_data = get_user_by_id($user_id);
    if (!$user_data) return ['success' => false, 'message' => 'Gagal mendapatkan data pengguna.'];
    if (!password_verify($password_saat_ini, $user_data['password'])) return ['success' => false, 'message' => 'Password saat ini salah.'];
    $hashed_password_baru = password_hash($password_baru, PASSWORD_DEFAULT);
    $stmt_update_pass = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt_update_pass, "si", $hashed_password_baru, $user_id);
    if (mysqli_stmt_execute($stmt_update_pass)) return ['success' => true, 'message' => 'Password berhasil diperbarui!'];
    return ['success' => false, 'message' => 'Gagal memperbarui password.'];
}

// FUNGSI MANAJEMEN KOMENTAR
function get_komentar_for_produk($product_id)
{
    global $conn;
    $sql = "SELECT k.*, u.username, u.foto_profil FROM komentar_produk k JOIN users u ON k.user_id = u.id WHERE k.product_id = ? ORDER BY k.created_at DESC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $product_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $komentar_list = [];
    while ($row = mysqli_fetch_assoc($result)) $komentar_list[] = $row;
    mysqli_stmt_close($stmt);
    return $komentar_list;
}
function tambah_komentar($product_id, $user_id, $komentar)
{
    global $conn;
    $komentar = htmlspecialchars(trim($komentar));
    if (empty($komentar)) return ['success' => false, 'message' => 'Komentar tidak boleh kosong.'];
    $stmt = mysqli_prepare($conn, "INSERT INTO komentar_produk (product_id, user_id, komentar) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "iis", $product_id, $user_id, $komentar);
    if (mysqli_stmt_execute($stmt)) return ['success' => true, 'message' => 'Komentar berhasil ditambahkan.'];
    return ['success' => false, 'message' => 'Gagal menambahkan komentar.'];
}
function hapus_komentar_admin($id_komentar)
{
    global $conn;
    $stmt = mysqli_prepare($conn, "DELETE FROM komentar_produk WHERE id_komentar = ?");
    mysqli_stmt_bind_param($stmt, "i", $id_komentar);
    if (mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) > 0) return ['success' => true, 'message' => 'Komentar berhasil dihapus.'];
    return ['success' => false, 'message' => 'Gagal menghapus komentar.'];
}

// FUNGSI MANAJEMEN KERANJANG BELANJA (DATABASE)
function tambah_ke_keranjang_db($user_id, $product_id, $kuantitas)
{
    global $conn;
    $produk = get_produk_by_id($product_id);
    if (!$produk) return ['success' => false, 'message' => 'Produk tidak ditemukan.'];
    $kuantitas_di_keranjang = 0;
    $stmt_check = mysqli_prepare($conn, "SELECT kuantitas FROM keranjang WHERE user_id = ? AND product_id = ?");
    mysqli_stmt_bind_param($stmt_check, "ii", $user_id, $product_id);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);
    if (mysqli_num_rows($result_check) > 0) $kuantitas_di_keranjang = mysqli_fetch_assoc($result_check)['kuantitas'];
    mysqli_stmt_close($stmt_check);
    if (($kuantitas_di_keranjang + $kuantitas) > $produk['stock']) return ['success' => false, 'message' => 'Stok tidak mencukupi.'];
    if ($kuantitas_di_keranjang > 0) {
        $kuantitas_baru = $kuantitas_di_keranjang + $kuantitas;
        $stmt_update = mysqli_prepare($conn, "UPDATE keranjang SET kuantitas = ? WHERE user_id = ? AND product_id = ?");
        mysqli_stmt_bind_param($stmt_update, "iii", $kuantitas_baru, $user_id, $product_id);
        $success = mysqli_stmt_execute($stmt_update);
        return ['success' => $success, 'message' => $success ? 'Kuantitas diperbarui.' : 'Gagal.'];
    } else {
        $stmt_insert = mysqli_prepare($conn, "INSERT INTO keranjang (user_id, product_id, kuantitas) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt_insert, "iii", $user_id, $product_id, $kuantitas);
        $success = mysqli_stmt_execute($stmt_insert);
        return ['success' => $success, 'message' => $success ? 'Produk ditambahkan.' : 'Gagal.'];
    }
}
function update_kuantitas_db($user_id, $product_id, $kuantitas_baru)
{
    global $conn;
    $produk = get_produk_by_id($product_id);
    if (!$produk) {
        $_SESSION['error_message_cart'] = "Produk tidak ditemukan.";
        return false;
    }
    if ($kuantitas_baru > $produk['stock']) {
        $_SESSION['error_message_cart'] = "Stok untuk produk \"" . htmlspecialchars($produk['name']) . "\" tidak mencukupi (tersedia: " . $produk['stock'] . ").";
        return false;
    }
    $stmt = mysqli_prepare($conn, "UPDATE keranjang SET kuantitas = ? WHERE user_id = ? AND product_id = ?");
    mysqli_stmt_bind_param($stmt, "iii", $kuantitas_baru, $user_id, $product_id);
    return mysqli_stmt_execute($stmt);
}
function ambil_keranjang_dari_db($user_id)
{
    global $conn;
    $sql = "SELECT k.product_id, k.kuantitas, p.name AS nama, p.price AS harga, p.image AS gambar, p.stock FROM keranjang k JOIN product p ON k.product_id = p.id WHERE k.user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $items = [];
    while ($row = mysqli_fetch_assoc($result)) $items[$row['product_id']] = $row;
    return $items;
}
function hapus_item_keranjang_db($user_id, $product_id)
{
    global $conn;
    $stmt = mysqli_prepare($conn, "DELETE FROM keranjang WHERE user_id = ? AND product_id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $product_id);
    return mysqli_stmt_execute($stmt);
}
function kosongkan_keranjang_db($user_id)
{
    global $conn;
    $stmt = mysqli_prepare($conn, "DELETE FROM keranjang WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    return mysqli_stmt_execute($stmt);
}
function hitung_item_keranjang_db($user_id)
{
    global $conn;
    if (!$conn || !isset($user_id)) return 0;
    $stmt = mysqli_prepare($conn, "SELECT SUM(kuantitas) as total_items FROM keranjang WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    return (int)($row['total_items'] ?? 0);
}

// FUNGSI MANAJEMEN PESANAN
function buat_pesanan($user_id, $data_pengiriman)
{
    global $conn;
    if (!isset($conn) || !$conn) return ['success' => false, 'message' => 'Koneksi DB gagal.'];
    $keranjang_items = ambil_keranjang_dari_db($user_id);
    if (empty($keranjang_items)) return ['success' => false, 'message' => 'Keranjang belanja Anda kosong.'];

    $nama_penerima = htmlspecialchars(trim($data_pengiriman['nama_penerima']));
    $no_hp_penerima = htmlspecialchars(trim($data_pengiriman['no_hp_penerima']));
    $alamat_pengiriman = htmlspecialchars(trim($data_pengiriman['alamat_pengiriman']));
    $metode_pembayaran = htmlspecialchars(trim($data_pengiriman['metode_pembayaran']));
    if (empty($nama_penerima) || empty($no_hp_penerima) || empty($alamat_pengiriman) || empty($metode_pembayaran)) {
        return ['success' => false, 'message' => 'Harap lengkapi semua informasi pengiriman dan pembayaran.'];
    }

    mysqli_begin_transaction($conn);
    try {
        $total_harga = 0;
        foreach ($keranjang_items as $id => $item) {
            $produk_db = get_produk_by_id($id);
            if (!$produk_db || $item['kuantitas'] > $produk_db['stock']) {
                throw new Exception("Stok untuk produk \"" . htmlspecialchars($item['nama']) . "\" tidak mencukupi.");
            }
            $total_harga += $item['harga'] * $item['kuantitas'];
        }

        $stmt_pesanan = mysqli_prepare($conn, "INSERT INTO pesanan (user_id, total_harga, nama_penerima, no_hp_penerima, alamat_pengiriman, metode_pembayaran) VALUES (?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt_pesanan, "iissss", $user_id, $total_harga, $nama_penerima, $no_hp_penerima, $alamat_pengiriman, $metode_pembayaran);
        if (!mysqli_stmt_execute($stmt_pesanan)) throw new Exception("Gagal menyimpan data pesanan utama.");
        $id_pesanan_baru = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt_pesanan);

        $stmt_detail = mysqli_prepare($conn, "INSERT INTO detail_pesanan (pesanan_id, product_id, nama_produk_saat_pesan, harga_saat_pesan, kuantitas, subtotal) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt_update_stok = mysqli_prepare($conn, "UPDATE product SET stock = stock - ? WHERE id = ?");

        foreach ($keranjang_items as $id => $item) {
            $subtotal = $item['harga'] * $item['kuantitas'];
            mysqli_stmt_bind_param($stmt_detail, "iisiii", $id_pesanan_baru, $item['product_id'], $item['nama'], $item['harga'], $item['kuantitas'], $subtotal);
            if (!mysqli_stmt_execute($stmt_detail)) throw new Exception("Gagal menyimpan detail produk: " . htmlspecialchars($item['nama']));

            mysqli_stmt_bind_param($stmt_update_stok, "ii", $item['kuantitas'], $item['product_id']);
            if (!mysqli_stmt_execute($stmt_update_stok)) throw new Exception("Gagal memperbarui stok untuk produk: " . htmlspecialchars($item['nama']));
        }
        mysqli_stmt_close($stmt_detail);
        mysqli_stmt_close($stmt_update_stok);

        mysqli_commit($conn);
        kosongkan_keranjang_db($user_id);
        return ['success' => true, 'message' => 'Pesanan berhasil dibuat!', 'order_id' => $id_pesanan_baru];
    } catch (Exception $e) {
        mysqli_rollback($conn);
        return ['success' => false, 'message' => $e->getMessage()];
    }
}
function get_pesanan_by_user_id($user_id)
{
    global $conn;
    $stmt = mysqli_prepare($conn, "SELECT * FROM pesanan WHERE user_id = ? ORDER BY tanggal_pesanan DESC");
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $pesanan_list = [];
    while ($row = mysqli_fetch_assoc($result)) $pesanan_list[] = $row;
    mysqli_stmt_close($stmt);
    return $pesanan_list;
}

function get_pesanan_by_id($id_pesanan)
{
    global $conn;
    $id_pesanan = (int)$id_pesanan;
    $sql = "SELECT p.*, u.username 
            FROM pesanan p 
            JOIN users u ON p.user_id = u.id 
            WHERE p.id_pesanan = ?";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        error_log("Prepare statement failed (get_pesanan_by_id): " . mysqli_error($conn));
        return null;
    }
    mysqli_stmt_bind_param($stmt, "i", $id_pesanan);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $data;
}

function get_pesanan_by_id_admin($id_pesanan)
{
    global $conn;
    $id_pesanan = (int)$id_pesanan;
    $sql = "SELECT p.*, u.username 
            FROM pesanan p 
            JOIN users u ON p.user_id = u.id 
            WHERE p.id_pesanan = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id_pesanan);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}
function get_detail_pesanan_items($id_pesanan)
{
    global $conn;
    $id_pesanan = (int)$id_pesanan;
    $sql = "SELECT d.*, p.image 
            FROM detail_pesanan d 
            LEFT JOIN product p ON d.product_id = p.id 
            WHERE d.pesanan_id = ?";
    return query(str_replace('?', $id_pesanan, $sql));
}
function update_status_pesanan($id_pesanan, $new_status)
{
    global $conn;
    $id_pesanan = (int)$id_pesanan;
    $allowed_statuses = ['Menunggu Pembayaran', 'Diproses', 'Dikirim', 'Selesai', 'Dibatalkan'];
    if (!in_array($new_status, $allowed_statuses)) {
        return false;
    }

    $stmt = mysqli_prepare($conn, "UPDATE pesanan SET status_pesanan = ? WHERE id_pesanan = ?");
    mysqli_stmt_bind_param($stmt, "si", $new_status, $id_pesanan);
    return mysqli_stmt_execute($stmt);
}
