<x-mail::message>
# Halo!

@if ($mailData['type'] == 'activation')
## Yuk, mulai aktivasi akun AFM kamu.

Sedikit lagi akunmu akan aktif. Cukup masukkan kode verifikasi di bawah untuk mengaktifkan akunmu.
@elseif ($mailData['type'] == 'resetPassword')
Masukkan kode berikut untuk melakukan perubahan kata sandi.
@endif


<div style="text-align: center; font-size: 1.25rem; font-weight: bold; margin-bottom: 0.8rem;">{{ $mailData['otp'] }}</div>


Kode di atas hanya berlaku 10 menit. Mohon jangan sebarkan kode ini ke siapapun, termasuk pihak yang mengatas namakan AFM Shop.

Terimakasih,

{{ config('app.name') }}
</x-mail::message>