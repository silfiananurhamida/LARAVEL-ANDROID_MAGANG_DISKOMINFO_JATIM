package com.silfi.peminjaman_ruang;

public class BookingStatus {
    private String nip;
    private String nama;
    private String tanggal_mulai;
    private String tanggal_selesai;
    private String keperluan;

    public BookingStatus(String nip, String nama, String tanggal_mulai, String tanggal_selesai, String keperluan) {
        this.nip = nip;
        this.nama = nama;
        this.tanggal_mulai = tanggal_mulai;
        this.tanggal_selesai = tanggal_selesai;
        this.keperluan = keperluan;
    }

    public String getNip() {
        return nip;
    }

    public String getNama() {
        return nama;
    }

    public String getTanggal_mulai() {
        return tanggal_mulai;
    }

    public String getTanggal_selesai() {
        return tanggal_selesai;
    }

    public String getKeperluan() {
        return keperluan;
    }
}
