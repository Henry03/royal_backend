<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Leave</title>
    <style>
        .text-center {
            text-align: center;
        }

        .text-left {
            text-align: left;
        }

        .text-right {
            text-align: right;
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

        .m-0 {
            margin: 0;
        }

        .m-1 {
            margin: 0.25rem/* 4px */;
        }

        .m-8 {
            margin: 2rem/* 32px */;
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

        .font-semibold {
            font-weight: 600;
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
    
</head>
<body>
    <div class="text-center m-8">
        <img class="w-20 mx-auto" src="data:image/png;base64,{{ base64_encode(file_get_contents( 'http://192.168.77.209/royal_frontend/api/public/storage/logo.jpg' )) }}">
        <h2 class="text-xl text-center font-bold mb-5">Out of Duty Permit</h1>
        <table class="text-right w-full">
            <thead>
                <tr>
                    <th class="text-right">
                        {{ $data->reference_number}}
                    </th>
                </tr>
            </thead>
        </table>
        <table class="text-left">
            <tbody>
                <tr>
                    <td class="font-semibold text-base w-32">Name</td>
                    <td class="font-semibold text-base">:</td>
                    <td class="font-semibold text-base">{{ $staff->name}}</td>
                </tr>
                <tr>
                    <td class="font-semibold text-base">Position</td>
                    <td class="font-semibold text-base">:</td>
                    <td class="font-semibold text-base">{{ $staff->position}}</td>
                </tr>
                <tr>
                    <td class="font-semibold text-base">Department</td>
                    <td class="font-semibold text-base">:</td>
                    <td class="font-semibold text-base">{{ $staff->unit}}</td>
                </tr>
                <tr>
                    <td class="font-semibold text-base">Date of Request</td>
                    <td class="font-semibold text-base">:</td>
                    <td class="font-semibold text-base">{{ $data->request_date}}</td>
                </tr>
            </tbody>
        </table>
        <table class="table table-fixed w-full mt-5">
            <thead>
                <tr>
                    <th class="border-e border-b border-slate-800 text-slate-800 text-center">Type of Leave</th>
                    <th class="border-e border-b border-slate-800 text-slate-800 text-center">Entitlement</th>
                    <th class="border-b border-slate-800 text-slate-800 text-center">Taken Period</th>
                </tr>
            </thead>
            <tbody class="border-b border-slate-800">
                @foreach($dp as $item)
                    <tr>
                        <th class="text-xs">Day Payment</th>
                        <td class="text-center">{{ $item->date }}</td>
                        <td class="text-center">{{ $item->date_replace }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tbody class="border-b border-slate-800">
                @foreach($eo as $item)
                    <tr>
                        <th class="text-xs">Extra Off</th>
                        <td class="text-center">{{ $item->date }}</td>
                        <td class="text-center">{{ $item->date_replace }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tbody>
                @foreach($al as $item)
                    <tr>
                        <th class="text-xs">Annual Leave</th>
                        <td class="text-center">{{ $item->date }}</td>
                        <td class="text-center">{{ $item->date_replace }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        <table class="table w-full mt-5">
                <thead>
                    <tr>
                        <th class="border-e border-b border-slate-800 text-slate-800 text-center">Type of Leave</th>
                        <th class="border-e border-b border-slate-800 text-slate-800 text-center">Outstanding</th>
                        <th class="border-e border-b border-slate-800 text-slate-800 text-center">Validity</th>
                        <th class="border-e border-b border-slate-800 text-slate-800 text-center">Approval</th>
                        <th class="border-b border-slate-800 text-slate-800 text-center">Balance</th>

                    </tr>
                </thead>
                <tbody>
                        <tr>
                            <th class="text-xs">Day Payment</th>
                            <td class="text-center">{{ $quota['dp']['outstanding'] }}</td>
                            <td class="text-center">{{ $quota['dp']['validity'] }}</td>
                            <td class="text-center">{{ $quota['dp']['approval'] }}</td>
                            <td class="text-center">{{ $quota['dp']['balance'] }}</td>
                        </tr>
                        <tr>
                            <th class="text-xs">Extra Off</th>
                            <td class="text-center">{{ $quota['eo']['outstanding'] }}</td>
                            <td class="text-center">{{ $quota['eo']['validity'] }}</td>
                            <td class="text-center">{{ $quota['eo']['approval'] }}</td>
                            <td class="text-center">{{ $quota['eo']['balance'] }}</td>
                        </tr>
                        <tr>
                            <th class="text-xs">Annual Leave</th>
                            <td class="text-center">{{ $quota['al']['outstanding'] }}</td>
                            <td class="text-center">{{ $quota['al']['validity'] }}</td>
                            <td class="text-center">{{ $quota['al']['approval'] }}</td>
                            <td class="text-center">{{ $quota['al']['balance'] }}</td>
                        </tr>
                </tbody>
            </table>
            <table class="w-full text-left mt-5 mb-5">
                <tbody>
                    <tr>
                        <th class="w-16">
                            Note :
                        </th>
                        <td>
                            {{$data->note}}
                        </td>
                    </tr>
                </tbody>
            </table>
            <table class=" table border-collapse border table-fixed w-full mt-5">
                <thead class="">
                    <tr class="text-sm">
                        <th class="border">Employee</th>
                        @foreach($users as $user)
                            @if($user->role == 1)
                                <th class="border">Admin</th>
                            
                            @elseif($user->role == 2)
                                <th class="border">Chief</th>
                            
                            @elseif($user->role == 3)
                                <th class="border">Asst. HOD</th>
                            
                            @elseif($user->role == '4') 
                                <th class="border">Head of Department</th>
                            
                            @elseif($user->role == 5)
                                <th class="border">General Manager</th>
                            
                            @elseif($user->role == 6)
                                <th class="border">HRD</th>
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
                        <td class="border border-t-0 text-center text-sm">{{ $staff->name }}</td>
                        @foreach($users as $user)
                            <td class="border border-t-0 text-center text-sm">{{ $user->name}}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td class="border border-t-0 text-xs px-5">Date : {{ $data->created_at }}</td>
                        @foreach($users as $user)
                            <td class="border border-t-0 text-xs px-5">Date : {{ $user->created_at}}</td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
            <div class="text-sm text-left mt-5">
                <p class="m-0">* Procedure to be followed :</p>
                <div class="m-0">
                    <p class="m-0">1. Please submit your leave request at least 1 week prior to leave taken</p>
                    <p class="m-0">2. Only HOD / Asst. HOD / GM who can approve your leave request</p>
                    <p class="m-0">3. This approval can be changed at any time due to operasional needs</p>
                    <p class="m-0">4. Employee cannot take leave before approved by HOD / Asst. HOD, except for sick & death</p>
                </div>
                <p class="text-center">* This document is automatically generated by our system. Any alterations or unauthorized modifications are strictly prohibited</p>
            </div>
        </div>
    </div>   

</body>
</html>