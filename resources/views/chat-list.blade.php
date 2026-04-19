@extends('layouts.app')

@section('title', 'Chats | ChatApp')

@section('styles')
    
    @vite(['resources/css/chat.css'])
@endsection

@section('content')
    <livewire:chat />
@endsection
