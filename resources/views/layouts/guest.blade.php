<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        <style>
            /* Estilos básicos para substituir o Vite */
            *, ::before, ::after {
                box-sizing: border-box;
                border-width: 0;
                border-style: solid;
                border-color: #e5e7eb;
            }
            
            body {
                font-family: 'Figtree', sans-serif;
                margin: 0;
                padding: 0;
                color: #111827;
                background-color: #f3f4f6;
                line-height: 1.5;
                height: 100vh;
            }
            
            .min-h-screen {
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .flex {
                display: flex;
            }
            
            .flex-col {
                flex-direction: column;
            }
            
            .items-center {
                align-items: center;
            }
            
            .justify-center {
                justify-content: center;
            }
            
            .pt-6 {
                padding-top: 1.5rem;
            }
            
            .sm\:pt-0 {
                padding-top: 0;
            }
            
            .w-full {
                width: 100%;
            }
            
            .sm\:max-w-md {
                max-width: 28rem;
            }
            
            .mt-6 {
                margin-top: 1.5rem;
            }
            
            .px-6 {
                padding-left: 1.5rem;
                padding-right: 1.5rem;
            }
            
            .py-4 {
                padding-top: 1rem;
                padding-bottom: 1rem;
            }
            
            .bg-white {
                background-color: #ffffff;
            }
            
            .overflow-hidden {
                overflow: hidden;
            }
            
            .shadow-md {
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            }
            
            .sm\:rounded-lg {
                border-radius: 0.5rem;
            }
            
            .block {
                display: block;
            }
            
            .text-sm {
                font-size: 0.875rem;
                line-height: 1.25rem;
            }
            
            .font-medium {
                font-weight: 500;
            }
            
            .text-gray-700 {
                color: #374151;
            }
            
            .mt-1 {
                margin-top: 0.25rem;
            }
            
            .rounded-md {
                border-radius: 0.375rem;
            }
            
            .shadow-sm {
                box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            }
            
            .border-gray-300 {
                border-color: #d1d5db;
            }
            
            .focus\:border-indigo-500:focus {
                border-color: #6366f1;
            }
            
            .focus\:ring-indigo-500:focus {
                --tw-ring-color: #6366f1;
            }
            
            .focus\:ring-opacity-50:focus {
                --tw-ring-opacity: 0.5;
            }
            
            input[type="text"], input[type="email"], input[type="password"] {
                width: 100%;
                border-radius: 0.375rem;
                border: 1px solid #d1d5db;
                padding: 0.5rem 0.75rem;
            }
            
            input[type="text"]:focus, input[type="email"]:focus, input[type="password"]:focus {
                outline: none;
                border-color: #6366f1;
                box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2);
            }
            
            button {
                cursor: pointer;
            }
            
            .flex {
                display: flex;
            }
            
            .items-center {
                align-items: center;
            }
            
            .ml-3 {
                margin-left: 0.75rem;
            }
            
            .text-sm {
                font-size: 0.875rem;
                line-height: 1.25rem;
            }
            
            .text-gray-600 {
                color: #4b5563;
            }
            
            .hover\:text-gray-900:hover {
                color: #111827;
            }
            
            .underline {
                text-decoration: underline;
            }
            
            .flex {
                display: flex;
            }
            
            .items-center {
                align-items: center;
            }
            
            .justify-end {
                justify-content: flex-end;
            }
            
            .mt-4 {
                margin-top: 1rem;
            }
            
            .inline-flex {
                display: inline-flex;
            }
            
            .items-center {
                align-items: center;
            }
            
            .px-4 {
                padding-left: 1rem;
                padding-right: 1rem;
            }
            
            .py-2 {
                padding-top: 0.5rem;
                padding-bottom: 0.5rem;
            }
            
            .bg-gray-800 {
                background-color: #1f2937;
            }
            
            .border {
                border-width: 1px;
            }
            
            .border-transparent {
                border-color: transparent;
            }
            
            .rounded-md {
                border-radius: 0.375rem;
            }
            
            .font-semibold {
                font-weight: 600;
            }
            
            .text-xs {
                font-size: 0.75rem;
                line-height: 1rem;
            }
            
            .text-white {
                color: #ffffff;
            }
            
            .uppercase {
                text-transform: uppercase;
            }
            
            .tracking-widest {
                letter-spacing: 0.1em;
            }
            
            .hover\:bg-gray-700:hover {
                background-color: #374151;
            }
            
            .focus\:outline-none:focus {
                outline: none;
            }
            
            .focus\:border-gray-900:focus {
                border-color: #111827;
            }
            
            .focus\:ring:focus {
                --tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);
                --tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(3px + var(--tw-ring-offset-width)) var(--tw-ring-color);
                box-shadow: var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow, 0 0 #0000);
            }
            
            .focus\:ring-gray-300:focus {
                --tw-ring-color: #d1d5db;
            }
            
            .focus\:ring-opacity-50:focus {
                --tw-ring-opacity: 0.5;
            }
            
            .active\:bg-gray-900:active {
                background-color: #111827;
            }
            
            .disabled\:opacity-25:disabled {
                opacity: 0.25;
            }
            
            .bg-blue-500 {
                background-color: #3b82f6;
            }
            
            .hover\:bg-blue-700:hover {
                background-color: #1d4ed8;
            }
            
            .text-center {
                text-align: center;
            }
            
            .text-2xl {
                font-size: 1.5rem;
                line-height: 2rem;
            }
            
            .font-bold {
                font-weight: 700;
            }
            
            .mb-6 {
                margin-bottom: 1.5rem;
            }
            
            .text-blue-600 {
                color: #2563eb;
            }
            
            .mx-auto {
                margin-left: auto;
                margin-right: auto;
            }
            
            .login-container {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                width: 100%;
                max-width: 28rem;
            }
        </style>
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen bg-gray-100">
            <div class="login-container mx-auto">
                <div class="text-center mb-6">
                    <h1 class="text-2xl font-bold text-blue-600">FILA-IA</h1>
                </div>

                <div class="w-full sm:max-w-md px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
