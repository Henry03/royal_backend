<?php

use App\Http\Controllers\LoginAdminController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\StaffController;  
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\OutOfDutyPermitController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\LeavePermitController;
use App\Http\Controllers\LeaveUserController;
use App\Http\Controllers\ManagerOnDutyController;
use App\Http\Controllers\OffWorkController;
use App\Http\Controllers\OutOfDutyUserController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\StorageController;
use App\Http\Controllers\TelegramBotController;
use App\Http\Controllers\unitController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::controller(LoginController::class)->group( function() {
    Route::post('/login', 'login');
    Route::post('/otp', 'otp');
    Route::post('/authcheck', 'authcheck');
    Route::middleware('employee.token.check')->group(function () {
        Route::get('/logout', 'logout');
    });
});

Route::controller(LoginAdminController::class)->group( function() {
    Route::post('/loginadmin', 'login');
    
    Route::middleware(['auth:sanctum', 'ability:6'])->group(function () {
        Route::post('/admin/register', 'register');
    });

    Route::middleware(['auth:sanctum', 'ability:1,2,3,4,5,6'])->group(function () {
        Route::get('/admin', 'index');
        Route::get('/authcheckadmin', 'authCheck');
        Route::get('/admin/logout', 'logout');
    });
});

Route::controller(StaffController::class)->group( function() {
    Route::middleware(['auth:sanctum', 'ability:6'])->group(function () {
        Route::get('/staff/all', 'indexAll');
        Route::post('/staff', 'index');
        Route::post('/staff/detail', 'show');
        Route::post('/staff/store', 'store');
        Route::post('/staff/update', 'update');
        Route::post('/staff/delete', 'destroy');
        Route::post('/staff/upload', 'uploadCsv');
        Route::post('/staff/import', 'importArray');
        Route::post('/staff/count/newstaff', 'countNewStaff');
    });
    Route::middleware(['auth:sanctum', 'ability:1'])->group(function () {
        Route::get('/staff/department', 'indexbyDepartment');
    });
    Route::middleware(['auth:sanctum', 'ability:1,2,3,4,5,6'])->group(function () {
        Route::get('/user/profile', 'userProfile');
        Route::put('/user/profile', 'updateProfileUser');
    });
    Route::middleware('employee.token.check')->group(function () {
        Route::get('/profile', 'staffProfile');
        Route::put('/profile', 'updateProfile');
    });
});

Route::controller(UserController::class)->group( function() {
    Route::middleware(['auth:sanctum', 'ability:6'])->group(function () {
        Route::post('/user', 'index');
        Route::get('/user/detail/{id}', 'show');
        Route::put('/user/update/{id}', 'update');
        Route::post('/user/delete', 'destroy');
    });
});

Route::controller(DepartmentController::class)->group( function() {
    Route::middleware(['auth:sanctum', 'ability:6'])->group(function () {
        Route::get('/unit', 'indexUnit');
        Route::get('/position', 'indexPosition');
    });
});

Route::controller(EventController::class)->group( function() {
    Route::middleware(['auth:sanctum', 'ability:6'])->group(function () { 
        Route::post('/event/store', 'store');
        Route::put('/event/update/{id}', 'update');
        Route::get('/event/detail/{id}', 'show'); 
        Route::post('/event/delete', 'destroy');
    });
    Route::middleware(['auth:sanctum', 'ability:1,2,3,4,5,6'])->group(function () {
        Route::get('/event', 'index');
        Route::post('/eventbydate', 'indexByDate');
    });
    Route::middleware('employee.token.check')->group(function () {
        Route::get('/staff/event', 'index');
    });
});

Route::controller(OutOfDutyPermitController::class)->group( function() {
    Route::middleware('employee.token.check')->group(function () {
        Route::post('/outofduty', 'employeeIndex');
        Route::post('/outofduty/store', 'store');
        Route::get('/outofduty/detail/{id}', 'show');
        Route::put('/outofduty/cancel/{id}', 'employeeCancel');
        Route::get('/outofduty/download/{id}', 'download');
        Route::get('/outofduty/employee/approved', 'indexbyEmployeeApproved');
    });
    Route::middleware(['auth:sanctum', 'ability:5'])->group(function () {
        Route::post('/gm/outofduty', 'gmIndex');
    });
    Route::middleware(['auth:sanctum', 'ability:2,3,4'])->group(function () {
        Route::post('/user/outofduty', 'userIndex');
    });
    Route::middleware(['auth:sanctum', 'ability:6'])->group(function () {
        Route::post('/user/outofduty/all', 'Index');
        Route::get('/user/outofduty/calendar/all', 'indexAllCalendar');
    });
    Route::middleware(['auth:sanctum', 'ability:2,3,4,5,6'])->group(function () {
        Route::put('/user/outofduty/approve/{id}', 'approve');
        Route::put('/user/outofduty/reject/{id}', 'reject'); 
    });
    Route::middleware(['auth:sanctum', 'ability:1,2,3,4,5,6'])->group(function () {
        Route::get('/user/outofduty/detail/{id}', 'show');
        Route::get('/user/outofduty/department', 'departmentIndex');
    });
    Route::get('/outofduty/download', [OutOfDutyPermitController::class, 'download']);
});

Route::controller(OutOfDutyUserController::class)->group( function() {
    Route::middleware(['auth:sanctum', 'ability:1,2,3,4,5,6'])->group(function () {
        Route::post('/outofduty/user', 'indexByEmployee');
        Route::get('/outofduty/user/detail/{id}', 'show');
        Route::put('/outofduty/user/cancel/{id}', 'cancel');
        Route::post('/outofduty/user/store', 'store');
    });
});

Route::controller(LeaveUserController::class)->group( function() {
    Route::middleware(['auth:sanctum', 'ability:1,2,3,4,5,6'])->group(function () {
        Route::post('/leavepermit/user', 'indexByEmployee');
        Route::get('/leavepermit/user/quota', 'indexByEmployeeQuota');
        Route::get('/leavepermit/user/detail/{id}', 'show');
        Route::put('/leavepermit/user/cancel/{id}', 'cancel');
        Route::post('/leavepermit/user/store', 'store');
    });
});

Route::controller(AttendanceController::class)->group( function() {
    Route::middleware('employee.token.check')->group(function () {
        Route::post('/attendance', 'indexbyEmployee');
    });
    Route::middleware(['auth:sanctum', 'ability:6'])->group(function () {
        Route::post('/user/attendance', 'indexAll');
    });
});

Route::controller(ManagerOnDutyController::class)->group( function() {
    Route::middleware(['auth:sanctum', 'ability:1'])->group(function () {
        Route::post('/user/manageronduty/department', 'indexbyDeparment');
        Route::post('/user/manageronduty/departmentmod', 'indexbyDeparmentMod');
        // Route::post('/user/manageronduty/store', 'store');
        Route::get('/user/manageronduty/detail/{id}', 'show');
        Route::post('/user/manageronduty/store', 'update');
        Route::post('/user/manageronduty/delete', 'destroy');
        Route::post('/user/manageronduty/department/calendar', 'calendar');
    });
});

Route::controller(ShiftController::class)->group( function() {
    Route::middleware(['auth:sanctum', 'ability:1'])->group(function () {
        Route::post('/user/shift/store', 'store');
        Route::post('/user/shift/delete', 'destroy');
    });
});


Route::controller(OffWorkController::class)->group( function() {
    Route::middleware(['auth:sanctum', 'ability:1'])->group(function () {
        Route::get('/offwork/department', 'countByDepartment');
    });

    Route::middleware(['auth:sanctum', 'ability:1'])->group(function () {
        Route::post('/offwork/dp', 'indexDp');
        Route::post('/offwork/eo', 'indexEo');
        Route::post('/offwork/al', 'indexAl');
    });
});

Route::controller(LeavePermitController::class)->group(function() {
    Route::middleware('employee.token.check')->group(function () {
        Route::post('/leavepermit/employee', 'indexbyEmployee');
        Route::get('/leavepermit/employee/quota', 'indexEmployeeQuota');
        Route::post('/leavepermit/store', 'store');
        Route::get('/leavepermit/detail/{id}', 'show');
        Route::put('/leavepermit/cancel/{id}', 'cancel');
        Route::get('/leavepermit/employee/approved', 'indexbyEmployeeApproved');
        Route::get('/leavepermit/count/quota/employee', 'countEmployeeQuota');
    });

    Route::middleware(['auth:sanctum', 'ability:1,2,3,4,5,6'])->group(function () {
        Route::post('/user/leavepermit/department', 'indexbyDepartment');
        Route::post('/user/leavepermit/hrd', 'indexbyHrd');
        Route::get('/user/leavepermit/detail/{id}', 'show');
        Route::put('/user/leavepermit/reject/{id}', 'reject');
        Route::put('/user/leavepermit/approve/{id}', 'approve');
        Route::put('/user/leavepermit/reject/dp/{id}', 'rejectDp');
        Route::put('/user/leavepermit/reject/eo/{id}', 'rejectEo');
        Route::put('/user/leavepermit/reject/al/{id}', 'rejectAl');
        Route::post('/user/leavepermit/department/approved', 'indexbyDepartmentApproved');
    });

    Route::middleware(['auth:sanctum', 'ability:1'])->group(function () {
        Route::post('/user/eo/department', 'indexEobyDepartment');
        Route::post('/user/eo/department/noeo', 'indexNoEobyDepartment');
        Route::post('/user/eo/store', 'storeEo');
    });

    Route::middleware(['auth:sanctum', 'ability:6'])->group(function () {
        Route::get('/user/leavepermit/calendar/all', 'indexAllCalendar');
    });

    Route::middleware(['auth:sanctum', 'ability:5'])->group(function () {
        Route::post('/gm/leavepermit', 'indexByGm');
    });

    Route::get('/leave/download', 'download');
});

Route::controller(unitController::class)->group( function() {
    Route::middleware(['auth:sanctum', 'ability:6'])->group(function () {
        Route::get('/unit/index', 'index');
    });
});

Route::controller(StorageController::class)->group( function() {
    Route::get('/download/staff/template', 'downloadStaffTemplate');
    // Route::post('/attendance', 'indexAll');
});

Route::controller(TelegramBotController::class)->group( function() {
    Route::get('/telegram/staff', 'indexStaff');
    Route::get('/telegram/getme', 'getMe');
    Route::get('/telegram/setwebhook', 'setWebhook');
    Route::post('/telegram/webhook', 'commandHandlerWebhook');
    Route::get('/telegram/webhook/delete', 'deleteWebhook');
    Route::get('/telegram/webhook/info', 'getWebhookInfo');
});
