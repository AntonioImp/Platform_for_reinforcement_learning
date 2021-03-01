@extends('layouts.app')

@section('script')
    <script src="{{ asset('js/request.js') }}" defer></script>
    <script src="{{ asset('js/archive.js') }}" defer></script>
@endsection

@section('style')
    <link href="{{ asset('css/styleContent.css') }}" rel="stylesheet">
@endsection

@section('nameApp')
    <h2>Choose training to view</h2>
@endsection

@section('content')
<section id="modal-view" class="hidden">
    <div id="confirm">
        <h2>Confirm?</h2>
        <div id="check">
            <h3 id="Y" data-id="">Yes</h3>
            <h3 id="N">No</h3>
        </div>
    </div>
</section>

<div id="oldTraining">

</div>
@endsection