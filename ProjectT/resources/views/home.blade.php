@extends('layouts.app')

@section('script')
    <script src="https://d3js.org/d3.v4.js" defer></script>
    <script src="{{ asset('js/request.js') }}" defer></script>
    <script src="{{ asset('js/lineChart.js') }}" defer></script>
    <script src="{{ asset('js/home.js') }}" defer></script>
    <script src="{{ asset('videoJS/video.min.js') }}" defer></script>
@endsection

@section('style')
    <link href="{{ asset('css/styleContent.css') }}" rel="stylesheet">
    <link href="{{ asset('videoJS/video-js.min.css') }}" rel="stylesheet">
@endsection

@section('nameApp')
    <h2>Welcome {{ auth()->user()->username }}</h2>
    <span id="charge" class="hidden" data-id={{ $id ?? '' }}></span>
@endsection

@section('content')
<div class="content">
    <h1>Game: Pong</h1>
    {{--  <select name="game">
        <option value="1" selected>Pong</option>
        <option value="2">Cart pole</option>
    </select>  --}}
    <div id="puls">
        <div id="start">START</div>
        <div id="stop">STOP</div>
    </div>
    <div class="error">

    </div>
</div>

<div class="diagrams hidden">
    <svg id="duration" >
    </svg>

    <svg id="reward" >
    </svg>
</div>

<div class="video">
    <img src="{{ URL::asset('/images/novideo.jpg') }}" class="hidden" id="video1" data-position="">
    <img src="{{ URL::asset('/images/novideo.jpg') }}" class="hidden" id="video2" data-position="">
    <img src="{{ URL::asset('/images/novideo.jpg') }}" class="hidden" id="video3" data-position="">
    <img src="{{ URL::asset('/images/novideo.jpg') }}" class="hidden" id="video4" data-position="">
    <img src="{{ URL::asset('/images/novideo.jpg') }}" class="hidden" id="video5" data-position="">
    <img src="{{ URL::asset('/images/novideo.jpg') }}" class="hidden" id="video6" data-position="">
    <img src="{{ URL::asset('/images/novideo.jpg') }}" class="hidden" id="video7" data-position="">
    <img src="{{ URL::asset('/images/novideo.jpg') }}" class="hidden" id="video8" data-position="">
    <img src="{{ URL::asset('/images/novideo.jpg') }}" class="hidden" id="video9" data-position="">
    <img src="{{ URL::asset('/images/novideo.jpg') }}" class="hidden" id="video10" data-position="">
    <img src="{{ URL::asset('/images/novideo.jpg') }}" class="hidden" id="video11" data-position="">
    <img src="{{ URL::asset('/images/novideo.jpg') }}" class="hidden" id="video12" data-position="">
    <img src="{{ URL::asset('/images/novideo.jpg') }}" class="hidden" id="video13" data-position="">
    <img src="{{ URL::asset('/images/novideo.jpg') }}" class="hidden" id="video14" data-position="">
    <img src="{{ URL::asset('/images/novideo.jpg') }}" class="hidden" id="video15" data-position="">
    <img src="{{ URL::asset('/images/novideo.jpg') }}" class="hidden" id="video16" data-position="">
    <img src="{{ URL::asset('/images/novideo.jpg') }}" class="hidden" id="video17" data-position="">
    <img src="{{ URL::asset('/images/novideo.jpg') }}" class="hidden" id="video18" data-position="">
    <img src="{{ URL::asset('/images/novideo.jpg') }}" class="hidden" id="video19" data-position="">
    <img src="{{ URL::asset('/images/novideo.jpg') }}" class="hidden" id="video20" data-position="">
</div>

<section id="modal-view" class="hidden">
    <video class="video-js vjs-default-skin" poster="{{ URL::asset('/images/novideo.jpg') }}" controls>
        <source src="" type='video/webm'>
    </video>
</section>

@endsection
