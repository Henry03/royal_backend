<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Out of Duty</title>
    <style>
        .text-center {
            text-align: center;
        }

        .text-left {
            text-align: left;
        }

        .w-10 {
            width: 2.5rem;
        }

        .w-16 {
            width: 4rem;
        }

        .w-20 {
            width: 5rem;
        }

        .w-32 {
            width: 8rem;
        }

        .w-full {
            width: 100%;
        }

        .h-28 {
            height: 7rem/* 112px */;
        }

        .m-16 {
            margin: 4rem/* 64px */;
        }

        .m-28 {
            margin: 7rem/* 112px */;
        }

        .m-32 {
            margin: 8rem/* 128px */;
        }

        .mb-5 {
            margin-bottom: 1.25rem/* 20px */;
        }

        .mt-5 {
                margin-top: 1.25rem;
        }

        .mx-auto {
            margin-left: auto;
            margin-right: auto;
        }

        .text-xs {
            font-size: 0.75rem/* 12px */;
            line-height: 1rem/* 16px */;
        }

        .text-sm {
            font-size: 0.875rem/* 14px */;
            line-height: 1.25rem/* 20px */;
        }

        .text-xl {
            font-size: 1.25rem/* 20px */;
            line-height: 1.75rem/* 28px */;
        }

        .font-bold {
            font-weight: 700;
        }

        .border {
            border-width: 1px;
        }

        .border-collapse {
            border-spacing: 0;
        }

        .table-fixed {
            table-layout: fixed;
        }

        .table {
            border-left: 0.01em solid #ccc;
            border-right: 0;
            border-top: 0.01em solid #ccc;
            border-bottom: 0;
            border-collapse: collapse;
        }
        
        .table td,
        .table th {
            border-left: 0;
            border-right: 0.01em solid #ccc;
            border-top: 0;
            border-bottom: 0.01em solid #ccc;
        }

        .border-0 {
            border-width: 0px;
        }

        .border-t-0 {
            border-top: 0;
        }

        .border-b-0 {
            border-bottom: 0;
        }

        .border-y-0 {
            border-top: 0;
            border-bottom: 0;
        }

    </style>
    @vite('resources/css/app.css')
    
</head>
<body>
    <div class="text-center m-16">
        <img class="w-20 mx-auto" src="data:image/png;base64,{{ base64_encode(file_get_contents( 'http://192.168.77.209/royal_frontend/api/public/api/show/logo' )) }}">
        <h2 class="text-xl text-center font-bold mb-5">Out of Duty Permit</h1>
        <table class="text-left">
            <tbody>
                <tr>
                    <td class="text-base w-32">Name</td>
                    <td class="text-base">:</td>
                    <td class="text-base">{{ $data->name }}</td>
                </tr>
                <tr>
                    <td class="text-base">Date</td>
                    <td class="text-base">:</td>
                    <td class="text-base">{{ $data->date }}</td>
                </tr>
                <tr>
                    <td class="text-base">Time</td>
                    <td class="text-base">:</td>
                    <td class="text-base">{{ $data->start_time }} - {{$data->end_time}}</td>
                </tr>
                <tr>
                    <td class="text-base">Destination</td>
                    <td class="text-base">:</td>
                    <td class="text-base">{{ $data->destination }}</td>
                </tr>
                <tr>
                    <td class="text-base">Purpose</td>
                    <td class="text-base">:</td>
                    <td class="text-base">{{ $data->purpose }}</td>
                </tr>
                
            </tbody>
        </table>

        <table class="table table-fixed w-full mt-5">
            <thead class="">
                <tr class=" text-md">
                    <th class=" text-sm">Employee</th>
                    @foreach($users as $user)
                        @if($user->role == 1)
                            <th class=" text-sm">Admin</th>
                        
                        @elseif($user->role == 2)
                            <th class=" text-sm">Chief</th>
                        
                        @elseif($user->role == 3)
                            <th class=" text-sm">Asst. HOD</th>
                        
                        @elseif($user->role == '4') 
                            <th class=" text-sm">Head of Department</th>
                        
                        @elseif($user->role == 5)
                            <th class=" text-sm">General Manager</th>
                        
                        @elseif($user->role == 6)
                            <th class=" text-sm">HRD</th>
                        @endif
                    @endforeach
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="h-28 border-b-0">
                    </td>
                    @foreach($users as $user)
                        <td class="h-28"></td>
                    @endforeach
                </tr>
                <tr>
                    <td class="border-t-0 text-center text-sm">{{ $data->name }}</td>
                    @foreach($users as $user)
                        <td class="  text-center text-sm">{{ $user->name}}</td>
                    @endforeach
                </tr>
                <tr>
                    <td class="  text-xs px-5">Date : {{ $data->created_at }}</td>
                    @foreach($users as $user)
                        <td class="  text-xs px-5">Date : {{ $user->created_at}}</td>
                    @endforeach
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>