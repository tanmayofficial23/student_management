
@component('mail::message')

{{ $msg }}

@component('mail::button', ['url' => $url])
{{ $buttonText }}
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent