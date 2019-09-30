
@component('mail::message')

<h2>This email is sent to you because you requested to reset your password.</h2>

@component('mail::button', ['url' => $url])
Reset Password
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent