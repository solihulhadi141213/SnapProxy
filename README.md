# SnapProxy
SnapProxy adalah sebuah aplikasi proxy gateway yang dirancang untuk mempermudah integrasi layanan pembayaran menggunakan Midtrans Payment Gateway di lingkungan pengembangan lokal.
Midtrans secara default mewajibkan penggunaan protokol HTTPS untuk proses otentikasi dan pembuatan Snap Token. Hal ini seringkali menyulitkan developer yang melakukan uji coba di lingkungan http://localhost
Dengan SnapProxy, permasalahan tersebut dapat diatasi. Melalui mekanisme perantara (reverse proxy) yang aman dan transparan. Aplikasi ini bertindak sebagai jembatan antara aplikasi lokal developer (yang berjalan di HTTP) dengan server Midtrans (yang mewajibkan HTTPS).

## Service Request
Setiap request terhadap endpoint aplikasi Snap Proxy selalu disretai dengan ijin akses menggunakan x-token pada header. Nilai x-token tersebut dapat diperoleh dengan cara mengirimkan request menggunakan metode POST disertai payload JSON yang berisikan USER_KEY dan SECRET_KEY.
Adapun akun default pada saat pertama kali aplikasi diinstal sebagai berikut.

USER_KEY : 3mQKUd4ikicxxG3EQHVy6LcjSiHV8IlRXYgP
SECRET_KEY : 1VttZESUj7m2l1cLOq2nYUl6wpZddWw4tEOq

## Fitur Dan Spesifikasi
