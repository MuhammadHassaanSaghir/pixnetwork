@component('mail::message')
    <h1 style="text-align: center;"> pixNetwork </h1>
    <h3 style="text-transform: capitalize;">Reset Your Password</h3>
@component('mail::button', ['url' => $url])
    Reset Password!
@endcomponent  
    Thanks
@endcomponent