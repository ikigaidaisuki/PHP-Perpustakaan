<?php
	require_once '../setting/koneksi.php';
	require_once '../setting/session.php';
	require('../lib/tcpdf/tcpdf.php');

	$usersession = $_SESSION['login_user'];
	$awal = mysqli_real_escape_string($db,$_POST['tglawal']);
	$akhir = mysqli_real_escape_string($db,$_POST['tglakhir']);
	
	if($awal == null && $akhir == null){
		$where = "";
	}else if($awal != null && $akhir == null){
		$where = " AND tgl_pinjam = '$awal'";
	}else if($awal != null & $akhir != null){
		$where = " AND tgl_pinjam >= '$awal' AND tgl_pinjam <= '$akhir' ";
	}
	$print_token = md5(uniqid($usersession, true));

	$tgl_cetak = date("Y-m-d h:i:sa");
	// create new PDF document
	$pdf = new TCPDF('L', PDF_UNIT, 'A3', true, 'UTF-8', false);
			
	// set document information
	$pdf->SetTitle('Laporan Peminjaman');
	$pdf->SetSubject('Laporan Peminjaman');
			
	// set margins
	$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
	$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
	$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
			
	// set auto page breaks
	$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    // add a page
	$pdf->AddPage();

	$pdf->SetFont('helvetica', 'B', 26);
	$pdf->Write(0, 'Laporan Peminjaman - Perpustakaan 5F', '', 0, 'C', true, 0, false, false, 0);
	$pdf->Write(0, 'Periode : '.$awal." - ".$akhir, '', 0, 'L', true, 0, false, false, 0);
	$pdf->Write(0, '', '', 0, 'L', true, 0, false, false, 0);
			
	$pdf->SetFont('helvetica', '', 20);
	$pdf->Write(0, 'Tanggal cetak  : '.$tgl_cetak.'', '', 0, 'L', true, 0, false, false, 0);
	$pdf->Write(0, 'Security Print  : '.$print_token.'', '', 0, 'L', true, 0, false, false, 0);
	$pdf->Write(0, '', '', 0, 'L', true, 0, false, false, 0);
	$pdf->Write(0, '', '', 0, 'L', true, 0, false, false, 0);
			
	$pdf->SetFont('helvetica', '', 18);
			
	$sql = "SELECT * FROM v_peminjaman WHERE 1=1 $where";
	//echo $sql;
	$result = mysqli_query($db,$sql);
	
	$isi .= <<<EOD
				<table border="0.5">
				<tr>
					<th><b>No</b></th>
					<th><b>No Peminjaman</b></th>
					<th><b>Staff</b></th>
					<th><b>Tanggal Pinjam</b></th>
					<th><b>Tanggal Kembali</b></th>
					<th><b>No Anggota</b></th>
					<th><b>Nama Anggota</b></th>
					<th><b>Jumlah Buku</b></th>
				</tr>
EOD;

	$no = 1;
			while ($tampil = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
				$isi.="	 	 	
					<tr>
						<td>".$no."</td>
						<td>".$tampil['no_peminjaman']."</td>
						<td>".$tampil['staff']."</td>
						<td>".$tampil['tgl_pinjam']."</td>
						<td>".$tampil['tgl_kembali']."</td>
						<td>".$tampil['no_anggota']."</td>
						<td>".$tampil['anggota']."</td>
						<td>".$tampil['jum']."</td>";
					$isi .="</tr>";
					$no++;
				}
	$isi.="</table>";
	
	$pdf->writeHTML($isi, true, false, false, false, '');
					
	$namaPDF = 'Laporan Peminjaman_'.$tgl_cetak.'.pdf';
	$pdf->Output($namaPDF,'I');
?>