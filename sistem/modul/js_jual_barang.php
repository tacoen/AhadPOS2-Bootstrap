<?php
/* js_jual_barang.php ----------------------------------------
version: 1.01

Part of AhadPOS : http://ahadpos.com
License: GPL v2
http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
http://vlsm.org/etc/gpl-unofficial.id.html

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License v2 (links provided above) for more details.
---------------------------------------------------------------- */
require_once($_SERVER["DOCUMENT_ROOT"].'/define.php');
include SITE_ROOT."sistem/modul/function.php";
session_start();
check_ahadpos_session();

if (!isset($_SESSION[idCustomer])) { findCustomer($_POST[idCustomer]); }

	$result=mysql_query("select `value` from config where `option`='ukm_mode'") or die(mysql_error());
	$hasil=mysql_fetch_array($result);
	$ukmMode=$hasil['value'];

	$transferahad=false;
	if (($_POST['transferahad'] == 1) || ($_GET['transferahad'] == 1)) {
		$transferahad=true;
	}

	ahp_kasirheader('Jual Barang','')?>
	<body class="kasir" id="dokumen">
			<div id="content" >
				<?php
				if ($_GET[doit] == 'hapus') {
					$hasil=mysql_query("select barcode from tmp_detail_jual where uid={$_GET['uid']}") or die('Gagal hapus (ambil data), error: ' . mysql_error());
					$r=mysql_fetch_array($hasil);

					$sql="DELETE FROM tmp_detail_jual WHERE barcode='{$r['barcode']}' and username='{$_SESSION['uname']}' and idCustomer={$_SESSION['idCustomer']}";
					// echo $sql;
					$hasil=mysql_query($sql) or die('Gagal hapus data, error: ' . mysql_error());
				}


				//fixme: hargaBeli TIDAK tersimpan di detail_jual !!!



				switch ($_GET[act]) { // ============================================================================================================
					case "caricustomer": ?>
						<div style="float:right" id="tot_pembelian">
							<span><?php echo number_format($_SESSION['tot_pembelian'], 0, ',', '.'); ?></span>
						</div>
						<?php if ($transferahad) { ?>
							<div class="top">
								Transfer Barang antar Ahad : <?php echo $_SESSION['namaCustomer']; ?> <br />
								<?php echo date('d-m-Y'); ?>
							</div>
						<?php } else { ?>
							<div class="top"><h3>
								Customer : <?php echo $_SESSION['namaCustomer']; ?>
								<?php
								if ($_SESSION['customerDiskonP'] > 0) {
									echo " ({$_SESSION['customerDiskonP']}%)";
								}
								elseif ($_SESSION['customerDiskonR'] > 0) {
									echo ' (' . number_format($_SESSION['customerDiskonR'], 0, ',', '.') . ')';
								}
								?>
							</h3></div>
							<!--<h3>Barang yang dijual</h3>-->
							<?php
						};
						?>
						<form id="entry-barang" method='post' action='js_jual_barang.php?act=caricustomer&action=tambah'>
							<div class="input-group">
								<label for="barcode">Barcode</label>
								<input type="text" class="form-control" name="barcode" accesskey="b" id="barcode">
							</div>
							<?php
							// ----- TERLALU LAMBAT ! ----- jangan gunakan dropbox terlampir untuk memilih barcode
							// ambil daftar barang
							//$sql="SELECT namaBarang,barcode,hargaJual
							//	FROM barang FORCE INDEX (barcode) ORDER BY barcode ASC";
							//$namaBarang=mysql_query($sql);
							//while($brg=mysql_fetch_array($namaBarang)){
							//	echo "<option value='$brg[barcode]'>$brg[barcode] - $brg[namaBarang] - Rp ".number_format($brg[hargaJual],0,',','.')."</option>\n";
							//}
							//var_dump($_POST);
							//var_dump($_GET);
							if ($transferahad) {
								?>
								<input type=hidden name='transferahad' value='1'>
								<?php
							}
							?>
							<?php
							/* ukmMode: menampilkan hargaBarang */
							if ($ukmMode) {
								?>
								<div class="input-group">
									<label for="hargaBarang">Harga</label>
									<input type="text" class="form-control" id="hargaBarang" name='hargaBarang' value='1' size=5 accesskey="h">
								</div>
								<?php
							}
							?>

							<div class="input-group">
								<label for="jumBarang">Qty</label>
								<input type="text" class="form-control" id="jumBarang" name='jumBarang' value='1' size=5 accesskey="q">
							</div>
						<div class="input-group">
							<br/>
							<button type="submit" class="btn btn-primary">Tambah</button>
						</div>
						</form>

						<form method="POST" action="js_cari_barang.php?caller=js_jual_barang" onSubmit="popupform(this, 'cari1')">
							<div class="input-group">
								<label for="namaBarang">Cari Barang</label>
								<input type="text" class="form-control" id="namaBarang" name='namabarang' accesskey='c'>
							</div>
							<?php
							if (($_POST['transferahad'] == 1) || ($_GET['transferahad'] == 1)) {
								?>
								<input type=hidden name='transferahad' value='1'>
								<?php
							};
							?>
							<!--<button type="submit" class="btn btn-primary" name="btnCari" id="btnCari">GO</button>-->
							<!--<input type='submit' class='btn btn-default' name='btnCari' id='btnCari' value='Cari'>-->
						</form>


						<script>
							var dropBox=document.getElementById("barcode");
							if (dropBox != null)
								dropBox.focus();
						</script>

						<?php
						if ($_GET[action] == 'tambah') {

							if ($_GET[barcode]) {
								$_POST[barcode]=$_GET[barcode];
							};
							/*
							* ukmMode: akan ada hargaBarang
							*/
							$hargaBarang=isset($_POST['hargaBarang']) ? $_POST['hargaBarang'] : NULL;
							/*
							$trueJual=cekBarangTempJual($_SESSION[idCustomer], $_POST[barcode]);
							//			echo "$trueJual";
							if ($trueJual != 0) {

							tambahBarangJualAda($_SESSION[idCustomer], $_POST[barcode], $_POST[jumBarang]);
							} else {

							tambahBarangJual($_POST[barcode], $_POST[jumBarang]);
							}
							*
							*/
							$tambahBarang=1;
							if (isset($_POST['jumBarang'])) {
								$tambahBarang=$_POST['jumBarang'];
							}
							$trueJual=cekBarangTempJual($_SESSION[idCustomer], $_POST[barcode]);
							// Jika barang sudah ada (hanya tambah kuantiti) maka tambahkan kuantitinya;
							if ($trueJual) {
								$jumBarang=$trueJual['jumBarang'];
								mysql_query("delete from tmp_detail_jual where idCustomer='{$_SESSION['idCustomer']}' "
												. "and barcode='{$_POST['barcode']}' "
												. "and username='{$_SESSION['uname']}'") or die('Gagal clean ' . mysql_error());
								$jumBarang += $tambahBarang;
							}
							else {
								$jumBarang=$tambahBarang;
							}
							tambahBarangJual($_POST['barcode'], $jumBarang, $hargaBarang);
						}
						$sql="SELECT *
								FROM tmp_detail_jual tdj, barang b
								WHERE tdj.barcode=b.barcode AND tdj.idCustomer='$_SESSION[idCustomer]' AND tdj.username='$_SESSION[uname]'";
						//echo $sql;
						$query=mysql_query($sql);
						$r=mysql_fetch_row($query);
						?>
						<hr />
						<?php
						if ($r) {
							//echo "Ada $r[0] data";
							?>
							<table class="tabel daftar-pembelian">
								<tr>
									<th>No</th>
									<th>Barcode</th>
									<th>Nama Barang</th>
									<th>Jumlah</th>
									<th>Harga</th>
									<th>Diskon</th>
									<th>Total</th>
									<th>Hapus</th>
								</tr>
								<?php
								$no=1;
								$tot_pembelian=0;

								$query2=mysql_query("SELECT tdj.uid, tdj.barcode, b.namaBarang, tdj.jumBarang, tdj.hargaJual, tdj.tglTransaksi, tdj.diskon_persen, tdj.diskon_rupiah
										FROM tmp_detail_jual tdj, barang b
										WHERE tdj.barcode=b.barcode AND tdj.idCustomer='{$_SESSION['idCustomer']}'
										AND tdj.username='{$_SESSION['uname']}' ORDER BY tdj.uid DESC");
								$banyakItem=mysql_num_rows($query2);
								while ($data=mysql_fetch_array($query2)) {

									// jika ini barang yang akan di transfer,
									// maka berikan hargaBeli (modal) sebagai hargaJual
									if (($_POST['transferahad'] == 1) || ($_GET['transferahad'] == 1)) {
										$sql="SELECT hargaBeli FROM detail_beli
										WHERE isSold='N' AND barcode='$data[barcode]' ORDER BY idDetailBeli ASC";
										$hasil=mysql_query($sql);
										$x=mysql_fetch_array($hasil);

										// jika tidak ada / semua stok barang ini sudah terjual=catatan stok ngaco
										// maka ambil hargaBeli yang terakhir saja
										if (mysql_num_rows($hasil) < 1) {
											$sql="SELECT hargaBeli FROM detail_beli
											WHERE barcode='$data[barcode]' ORDER BY idDetailBeli ASC";
											$hasil=mysql_query($sql);
											$x=mysql_fetch_array($hasil);
										};

										$data['hargaJual']=$x['hargaBeli'];
									};

									$total=$data['hargaJual'] * $data['jumBarang'];
									$totalDiskon=0;
									?>

									<tr class="<?php echo $no % 2 === 0 ? 'alt' : ''; ?>">
										<td class="right"><?php echo $banyakItem - $no + 1; ?></td>
										<td><?php echo $data[barcode]; ?></td>
										<td><?php echo $data[namaBarang]; ?></td>
										<td class="right"><?php echo $data['jumBarang']; ?></td>
										<td class="right">
											<?php
											// Jika punya hak admin, maka muncul link untuk manually update harga jual
											// Jika tidak, maka hanya muncul text statis harga jual
											if ($_SESSION['hakAdmin']) {
												?>
												<a href="#" class="harga-jual pilih" data-type="text" data-pk="<?php echo $data['uid']; ?>" data-url="../aksi.php?module=diskon&act=updatehj" ><?php echo $data['hargaJual']; ?></a>
												<?php
											}
											else {
												echo $data['hargaJual'];
											}
											?>
										</td>
										<td class="right"><?php
											if ($data['diskon_persen'] > 0) {
												echo $data['diskon_persen'] . '%';
											}
											elseif ($data['diskon_rupiah'] > 0) {
												echo number_format($data['diskon_rupiah'], 0, ',', '.');
												$totalDiskon += $data['diskon_rupiah'] * $data['jumBarang'];
											}
											?>
										</td>
										<td class="right"><?php echo number_format($total, 0, ',', '.'); ?></td>
										<?php /*
										<td class="right">
										<?php
										if ($data['diskon_persen'] > 0) {
										$totalPlusDiskon=$total - ($total * $data['diskon_persen'] / 100);
										echo number_format($totalPlusDiskon, 0, ',', '.');
										} elseif ($data['diskon_rupiah'] > 0) {
										$totalPlusDiskon=$total - $data['diskon_rupiah'];
										echo number_format($totalPlusDiskon, 0, ',', '.');
										} else {
										$totalPlusDiskon=$total;
										echo number_format($totalPlusDiskon, 0, ',', '.');
										}
										?>
										</td>
										*
										*/ ?>
										<td class="center"> <a class="pilih" href='js_jual_barang.php?act=caricustomer&doit=hapus&uid=<?php echo $data['uid']; ?><?php echo $transferahad ? '&transferahad=1':''; ?>'><i class="fa fa-times"></i></a></td>
									</tr>
									<?php
									$tot_pembelian += $total;
									$no++;
								}
								// Cek Diskon per Customer
								// Jika ada masukkan ke variabel $totalDiskon
								// $diskonCustomer=0;
								// $customerDiskonData=cekCustomerDiskon($_SESSION['idCustomer']);
								//echo $customerDiskonData['diskon_persen'];
								// PENTING!! Jika ada diskon persen, maka diskon harga (Rp) diabaikan
//								if ($customerDiskonData['diskon_persen'] > 0) {
//									$diskonCustomer=$tot_pembelian * $customerDiskonData['diskon_persen'] / 100;
//								} elseif ($customerDiskonData['diskon_rupiah'] > 0) {
//									$diskonCustomer=$customerDiskonData['diskon_rupiah'];
//								}
								// Total Pembelian di kurangi $diskonCustomer
								// $tot_pembelian -= $diskonCustomer;
								// $_SESSION['diskonCustomer']=$diskonCustomer;
								// end Diskon per Customer
								?>
							</table>
							<?php
							$pmbyrn=mysql_query("SELECT * from pembayaran");
							?>

							<form method='post' action='../aksi.php?module=penjualan_barang&act=input'>
								<input type=hidden name='tot_pembayaran' value="<?php echo $tot_pembelian; ?>" >
								<div class="kasir-kanan">
									<div id='kembalian'></div>
									<div class="pembayaran">
										<table class="pembayaran">
											<tr>
												<td class="right">Total Pembelian :</td>
												<td><div id='TotalBeli'><?php echo number_format($tot_pembelian, 0, ',', '.'); ?></div></td>
											</tr>

											<script>
												document.getElementById('tot_pembelian').innerHTML='<span><small><?php echo $diskonCustomer > 0 ? ' ' . number_format($diskonCustomer + $tot_pembelian, 0, ', ', '.') : ''; ?></small> <?php echo number_format($tot_pembelian, 0, ', ', '.'); ?> </span>';
											</script>

											<?php
											if ($transferahad) {
												?>
												<input type=hidden name=transferahad value=1>
												<?php
											}

											$_SESSION['tot_pembelian']=$tot_pembelian;
											?>
											<tr>
												<td class="right">Tipe Pembayaran :</td>
												<td class="">
													<select class='form-control' name='tipePembayaran' accesskey='a' tabindex=1>
														<option value='0'>-Tipe Pembayaran-</option>
														<?php
														while ($pembayaran=mysql_fetch_array($pmbyrn)) {
															if ($pembayaran[tipePembayaran] == 'CASH') {
																?>
																<option value="<?php echo $pembayaran['idTipePembayaran']; ?>" selected><?php echo $pembayaran['tipePembayaran']; ?></option>
																<?php
															}
															else {
																?>
																<option value="<?php echo $pembayaran['idTipePembayaran']; ?>" ><?php echo $pembayaran['tipePembayaran']; ?></option>
																<?php
															}
														}
														?>
													</select>
												</td>
											</tr>
											<tr>
												<td class="right">Surcharge :</td>
												<td class="surchange-td">
													<div class="input-group">
														<label for="surcharge">%</label>
														<input type='text' class='form-control' class='form-control' name='surcharge' id='surcharge' value=0 size=2 tabindex=2>
													</div>
													<div class="input-group">
														<label for="TotalSurcharge">Rp</label>
														<input type='text' class='form-control' class='form-control' name='TotalSurcharge' id='TotalSurcharge' value=0 size=6 tabindex=100 readonly>
													</div>
												</td>
											</tr>
											<tr>
												<td class="right">Uang Dibayar :</td>
												<td class=""><input type="text" class="form-control" accesskey="u" name="uangDibayar" id="uangDibayar" value="0" onBlur="RecalcTotal(<?php echo $tot_pembelian; ?>)" tabindex=3></td>
											</tr>
											<tr>
												<td class="right">Kembali :</td>
												<td class=""><input type='text' class='form-control' class='form-control' name='uangKembali' id='uangKembali' value=0></td>
											</tr>
											<tr>
												<td><a href='../aksi.php?module=penjualan_barang&act=batal' class="btn btn-sm btn-primary">Batal</a></td>
												<td class="right">&nbsp;<input type='submit' class='btn btn-primary' value='Simpan' onclick='this.form.submit();
																		this.disabled=true;'></td>
											</tr>
										</table>
									</div>
								</div>
							</form>
							<?php
						}
						else {
							?>
							Belum ada barang yang dibeli<br />
							<a href='../aksi.php?module=penjualan_barang&act=batal'><button>BATAL</button></a>
							<?php
						}
						/* logo pindah ke css, agar mudah di skin */
						break;
				}
			?>
			<div id="login-admin" style="
				display: none;
				position: fixed;
				border: 1px solid #a8cf45;
				bottom: 50px;
				background-color: #eef4d2;
				box-shadow: 0px 0px 4px 0px #d2e28b;
				padding: 15px;">
				<form>
					<input type="text" class="form-control" id="nama-user" name="nama-user" placeholder="Nama User Admin" /><br />
					<input class='form-control' type="password" id="password" name="password" placeholder="Password" /><br />
					<a href="js_jual_barang.php?act=caricustomer" class="btn btn-sm btn-primary" id="tombol-batal-login" accesskey="l">Bata<u>l</u></a>
					<input style="float: right" type="submit" class="btn btn-primary" id="tombol-login-submit" value="Submit" />
				</form>
			</div>
			<div id="self-checkout" style="
				display: none;
				position: fixed;
				border: 1px solid #a8cf45;
				bottom: 50px;
				background-color: #eef4d2;
				box-shadow: 0px 0px 4px 0px #d2e28b;
				padding: 15px;">
				<form id="form-sc">
					<input type="text" class="form-control" id="self-checkout-id" name="self-checkout-id" placeholder="Nomor Self Checkout" /><br />
					<!--<input class='form-control' type="password" id="password" name="password" placeholder="Password" /><br />-->
					<!--<a href="js_jual_barang.php?act=caricustomer" class="btn btn-sm btn-primary" id="tombol-batal-sc" accesskey="l">Bata<u>l</u></a>-->
					<input style="float: right" type="submit" class="btn btn-primary" id="tombol-login-submit" value="Submit" />
				</form>
			</div>
			<div id="footer" >
				<a class="btn btn-sm btn-primary" href="js_jual_barang.php?act=caricustomer<?php echo $transferahad ? '&transferahad=1':''; ?>" accesskey="r" >Reload</a>
				<a class="btn btn-sm btn-primary" href="" accesskey="d" id="admin-mode"<?php echo $_SESSION['hakAdmin'] ? 'style="background-color:#a8cf45;color:#fff"' : ''; ?>>
					<?php echo $_SESSION['hakAdmin'] ? '<i class="fa fa-power-off" style="color:green;"></i>' : '<i class="fa fa-power-off" ></i>'; ?> Admin Mode
				</a>
				<a class="btn btn-sm btn-primary" href="#" id="tombol-self-checkout" accesskey="f" >Self Checkout</a>
			</div>
			<script>
			
$(document).ready(function () {
	$('.harga-jual').editable({
		success: function (response, newValue) {
			var respon=JSON && JSON.parse(response) || $.parseJSON(response);
			if (respon.sukses) {
				window.location="js_jual_barang.php?act=caricustomer";
			} else {
				alert('hmm, error!')
			}
		}
	});
});

$("#tombol-self-checkout").click(function () {
	//$("#self-checkout").show(500);
	$("#self-checkout").toggle(500, function () {
		if ($("#self-checkout").css('display') === 'none') {
			console.log('hidden');
		} else {
			console.log("show");
			$("#self-checkout-id").val("");
			$("#self-checkout-id").focus();
		}
	});

	return false;
});

$("#form-sc").submit(function () {
	console.log($("#self-checkout-id").val());
	var datakirim={
		'sc-id': $("#self-checkout-id").val()
	};
	dataurl="../aksi.php?module=penjualan_barang&act=selfcheckoutinput";
	$.ajax({
		type: "POST",
		url: dataurl,
		data: datakirim,
		success: window.location="js_jual_barang.php?act=caricustomer"
	});
	$("#self-checkout").hide(500);
	return false;
})

$("#admin-mode").click(function () {

<?php if ($_SESSION['hakAdmin']) {?>
	var datakirim={ 'logout': 'iya' };
	dataurl="../aksi.php?module=diskon&act=loginadmin";
	$.ajax({
		type: "POST",
		url: dataurl,
		data: datakirim,
		success: window.location="js_jual_barang.php?act=caricustomer"
	});
<?php } else { ?>
	$("#login-admin").show(500);
	$("#nama-user").focus();
<?php } ?>
	return false;
});
$("#tombol-batal-login").click(function () {
	$("#login-admin").hide(500);
	return false;
});
$("#tombol-login-submit").click(function () {
	var datakirim={
		'username': $("#nama-user").val(),
		'pass': $("#password").val()
	}
	console.log(datakirim);
	dataurl="../aksi.php?module=diskon&act=loginadmin";
	$.ajax({
		type: "POST",
		url: dataurl,
		data: datakirim,
		success: function (data) {
			if (data === 'ketemu') {
window.location="js_jual_barang.php?act=caricustomer";
			} else {
alert('Login ditolak!');
			}
		},
	});
	return false;
});
<?php if ($ukmMode) { ?>

$("#barcode").keydown(function (e) {
	var datakirim={ barcode: $(this).val() };
	var dataurl='../aksi.php?module=penjualan_barang&act=get_harga_jual';
	if (e.keyCode === 13) {
		$.ajax({
			data: datakirim,
			url: dataurl,
			type: "POST",
			dataType: "json",
			success: function (data) {
				if (data.sukses) {
					$("#hargaBarang").val(data.hargaJual);
				} else {

				}
				$("#hargaBarang").focus();
				$("#hargaBarang").select();
			}
		});
		return false;
	}
});
<?php } ?>
</script>
</div>
</body>
</html>
<?php

// ukmMode: Barcode -> enter. Muncul Harga Jual

	/* CHANGELOG -----------------------------------------------------------
	1.6.0 / 2013-02-24 : Harry Sufehmi	: fitur : transfer barang antar sesama pengguna AhadPOS
	1.0.1 / 2010-06-03 : Harry Sufehmi	: perhitungan Surcharge dibetulkan
	0.9.2 / 2010-03-03 : Harry Sufehmi 	: initial release
	------------------------------------------------------------------------ */
