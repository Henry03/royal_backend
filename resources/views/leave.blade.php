<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Leave</title>
    @vite('resources/css/app.css')
    
</head>
<body>
    <div class="flex flex-col justify-center gap-5">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 517.81 326.51" height="50"><defs><style>.cls-1{fill:#a9a576;}</style></defs><g id="Layer_2" data-name="Layer 2"><g id="Layer_1-2" data-name="Layer 1"><path class="cls-1" d="M58.5,217.58c1-2.26,1.71-4.77,3.14-6.72,5.25-7.12,4-14.68-3.11-17.49-5.59-2.2-14.8-1.49-19.59,1.9-9.46,6.69-8.23,21.46.69,32.64,10.92,13.68,31.87,18.1,51.28,11.7,27.35-9,43.76-29.7,54.85-54.12,23.89-52.59,26.83-107.93,16.4-164.14-3.7-20-3.78-20.6,16.41-21,20.37-.42,41.76-.48,54.07,18.27,8.44,12.87,15.12,28.93,16.42,44.09,4.38,51.18-2,101.37-24.77,148.19-37.8,77.68-110.69,101.07-179.73,77C3.1,273.47-12.15,227,10.47,190.83c15.11-24.15,47.27-28.41,64-8.46,9,10.67,8.81,25.51-1.1,33.83C70,219,65,219.81,60.68,221.52Z"/><path class="cls-1" d="M458.58,219.09c-12.27,1.76-20.12-5.1-21.18-17.87-1-12.39,5.5-20.87,15.23-26.73,12-7.26,24.93-7.08,37.14-.21,30,16.84,37.28,62.53,15.27,89.88-19.45,24.17-45.84,31.37-74.94,32.33C363.73,298.7,310.67,260.78,285.57,193,268.22,146.1,263.81,97.7,271,48.37,275.31,18.59,295.05,1.7,325.11.45c34-1.43,34.07-1.42,28.4,31.76-9.48,55.43-3.69,109.08,21.84,159.34,15.47,30.48,38.49,52.68,76.44,50.33,18.32-1.13,33.83-15.88,32.22-32.37-.64-6.52-5.81-14.1-11.21-18.08-3.12-2.29-12.27-.08-16.16,3.16-6.81,5.68-4,13.21.78,19.76C458.29,215.56,458.21,217.46,458.58,219.09Z"/><path class="cls-1" d="M259.11,210.61c2.28,2.79,4.07,4.52,5.34,6.57,9.27,15,18.81,29.86,27.34,45.28,2,3.56,2.06,10.05.1,13.57-8.35,15-17.74,29.45-26.89,44-1.5,2.39-3.73,4.32-5.62,6.47-2.38-2.15-5.41-3.89-7-6.51-8.85-14.25-17.73-28.51-25.72-43.25-2-3.72-2.63-10.17-.75-13.68,8.31-15.54,17.71-30.5,26.89-45.57C254.19,215.15,256.54,213.35,259.11,210.61Z"/></g></g></svg>
        <h1 class="text-2xl text-center font-bold text-gray-800">Leave Request Form</h1>
        <div class="grid grid-flow-col justify-between gap-0">
            <div class="flex flex-col gap-1">
                <div class="flex flex-row items-center w-full gap-5">
                    <div class="font-semibold text-sm basis-2/3">Name</div>
                    <div class="font-semibold text-sm">:</div>
                    <div class="font-semibold text-sm w-full">{{ $staff->name}}</div>
                </div>
                <div class="flex flex-row items-center w-full gap-5">
                    <div class="font-semibold text-sm basis-2/3">Position</div>
                    <div class="font-semibold text-sm">:</div>
                    <div class="font-semibold text-sm w-full">{{ $staff->position}}</div>
                </div>
                <div class="flex flex-row items-center w-full gap-5">
                    <div class="font-semibold text-sm basis-2/3">Department</div>
                    <div class="font-semibold text-sm">:</div>
                    <div class="font-semibold text-sm w-full">{{ $staff->unit}}</div>
                </div>
                <div class="flex flex-row w-full gap-5">
                    <div class="font-semibold text-sm basis-2/3 whitespace-nowrap">Date of Request</div>
                    <div class="font-semibold text-sm">:</div>
                    <div class="font-semibold text-sm w-full white">{{ $data->request_date}}</div>
                </div>
            </div>
            <div>
                <div class="flex flex-col items-center w-full">
                    <!-- <div class="font-semibold text-sm ">Reference Number : </div> -->
                    <div class="font-semibold text-sm w-full">{{ $data->reference_number}}</div>
                </div>
            </div>
        </div>
        
        <div class="overflow-x-auto card border rounded-md border-slate-800">
            <table class="table">
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
                            <td>{{ $item->date }}</td>
                            <td>{{ $item->date_replace }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tbody class="border-b border-slate-800">
                    @foreach($eo as $item)
                        <tr>
                            <th class="text-xs">Extra Off</th>
                            <td>{{ $item->date }}</td>
                            <td>{{ $item->date_replace }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tbody>
                    @foreach($al as $item)
                        <tr>
                            <th class="text-xs">Annual Leave</th>
                            <td>{{ $item->date }}</td>
                            <td>{{ $item->date_replace }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>      
        <div class="overflow-x-auto card w-fit border rounded-md border-slate-800">
            <table class="table w-fit">
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
        </div>      
        
        
        <table class="border-collapse border table-fixed w-full mt-5">
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
                <tr class="h-28">
                    <td class="border border-b-0"></td>
                    <td class="border border-b-0"></td>
                    <td class="border border-b-0"></td>
                </tr>
                <tr>
                    <td class=""><hr class="mx-5"/></td>
                    <td class=""><hr class="mx-5"/></td>
                    <td class=""><hr class="mx-5"/></td>
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
        <div class="text-sm mt-5 grid gap-2">
            <p>* Procedure to be followed :</p>
            <div class="grid grid-flow-row ms-5">
                <p>1. Please submit your leave request at least 1 week prior to leave taken</p>
                <p>2. Only HOD / Asst. HOD / GM who can approve your leave request</p>
                <p>3. This approval can be changed at any time due to operasional needs</p>
                <p>4. Employee cannot take leave before approved by HOD / Asst. HOD, except for sick & death</p>
            </div>
            <p class="text-sm justify-center">* This document is automatically generated by our system. Any alterations or unauthorized modifications are strictly prohibited</p>
        </div>
        <div class="grid justify-center">
            <div class="w-fit">
            {!! QrCode::size(128)->merge('\public\storage\Logo.png')
                ->generate("http://192.168.77.209/royal_backend/public/leave/download?id={$id}&ids={$ids}") 
            !!}
            </div>
        </div>
    
    </div>
</body>
</html>