
@component('mail::message')

<h2>Click on the following button to verify your Email ID.</h2>

@component('mail::button', ['url' => $url])
Verify Mail
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent