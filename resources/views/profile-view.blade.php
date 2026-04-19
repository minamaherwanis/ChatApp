



@extends('layouts.app')

@section('title', 'Chats | Profile')

@section('styles')
    
    @vite(['resources/css/profile.css'])


@endsection

@section('content')
 <livewire:profile />
@endsection
