<?php

use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Crypt;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

if (!function_exists('saveRequestFile')) {
    function saveRequestFile($file, $name, $folder)
    {
        $title = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        // $extension = $file->getClientOriginalExtension();
        Storage::disk('local')->putFileAs($folder, $file, "$name");
        return "$title";
    }
}

if (!function_exists('deleteOldFile')) {
    function deleteOldFile($folder, $name)
    {
        Storage::disk('local')->delete($folder.$name);
        return "deleted";
    }
}
