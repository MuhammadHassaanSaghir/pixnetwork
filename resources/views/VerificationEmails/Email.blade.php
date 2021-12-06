@component('mail::message')
    <h1 style="text-align: center;"> pixNetwork </h1>
    <h3 style="text-transform: capitalize;"> {{ $name }} - Please Confirm your pixNetwork Account </h3>
@component('mail::button', ['url' => $url])
    Confirm!
@endcomponent  
    Thanks
@endcomponent