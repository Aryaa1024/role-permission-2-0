<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class RolePermissionController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $roles = Role::with('permissions')->get();

            return DataTables::of($roles)
                ->addIndexColumn()
                ->addColumn('permissions', function ($role) {
                    $modules = $role->permissions->groupBy(function ($permission) {
                        return explode('.', $permission->name, 2)[0];
                    });
                    $html = '';
                    foreach ($modules as $module => $permissions) {
                        $html .= '<div class="mb-2">';
                        $html .= '<strong class="fw-bolder">'.e(ucfirst($module)).': </strong><br>';
                        foreach ($permissions as $permission) {
                            $parts = explode('.', $permission->name, 2);
                            $permissionName = $parts[1] ?? '';
                            $html .= '
                                <div class="form-check form-check-inline">
                                    <input
                                        type="checkbox"
                                        class="form-check-input"
                                        disabled
                                        checked
                                    >
                                    <label class="form-check-label">
                                        '.e(ucfirst($permissionName)).'
                                    </label>
                                </div>
                            ';
                        }
                        $html .= '</div>';
                    }

                    return $html;
                })
                ->addColumn('actions', function ($role) {
                    $actions = '<div class="d-flex align-items-center gap-2">';
                    if (Auth::user()->can('roles.edit')) {
                        $actions .= '
                        <button class="btn btn-sm btn-outline-primary editBtn mb-2" data-id="'.$role->id.'">
                            <i class="bi bi-pencil"></i>
                        </button>
                    ';
                    }
                    if (Auth::user()->can('roles.destroy')) {
                        $actions .= '
                        <button class="btn btn-sm btn-outline-danger deleteBtn mb-2" data-id="'.$role->id.'">
                            <i class="bi bi-trash"></i>
                        </button>
                    ';
                    }
                    $actions .= '</div>';

                    return $actions ?? 'N/A';
                })
                ->rawColumns(['permissions', 'actions'])
                ->make(true);
        }
        $permissions = Permission::get();

        $permissionGroups = $permissions
            ->map(function ($permission) {

                $parts = explode('.', $permission->name, 2);

                return [
                    'id' => $permission->id,
                    'module' => $parts[0] ?? '',
                    'permission' => $parts[1] ?? '',
                    'name' => $permission->name,
                ];
            })
            ->filter(function ($permission) {
                return ! empty($permission['module'])
                    && ! empty($permission['permission']);
            })
            ->groupBy('module');

        return view('admin.roles', compact(['permissionGroups']));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role_name' => ['required', 'string', 'max:255', 'unique:roles,name'],

            'permissions' => ['required', 'array', 'min:1'],

            'permissions.*' => ['required', 'integer', 'exists:permissions,id'],
        ], [
            'role_name.required' => 'Role name is required.',
            'role_name.unique' => 'This role already exists.',

            'permissions.required' => 'Please select at least one permission.',
            'permissions.array' => 'Invalid permissions format.',
            'permissions.min' => 'Please select at least one permission.',
            'permissions.*.exists' => 'One or more selected permissions are invalid.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Please correct the validation errors.',
                'data' => [
                    'errors' => $validator->errors(),
                ],
            ], 422);
        }

        $validated = $validator->validated();

        try {
            DB::beginTransaction();

            $role = Role::create([
                'name' => $validated['role_name'],
            ]);

            $permissions = Permission::whereIn(
                'id',
                $validated['permissions']
            )->get();

            $role->syncPermissions($permissions);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Role created successfully.',
                'data' => [
                    'id' => $role->id,
                ],
            ], 201);
        } catch (\Throwable $e) {

            DB::rollBack();
            report($e);

            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong while creating the role.',
                'data' => null,
            ], 500);
        }
    }

    public function edit(int $id)
    {
        $role = Role::with('permissions')->find($id);

        if (! $role) {
            return response()->json([
                'status' => 'error',
                'message' => 'Role not found.',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Role fetched Successfully.',
            'data' => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('id')->values(),
            ],
        ]);
    }

    public function update(Request $request, int $id)
    {
        $role = Role::find($id);

        if (! $role) {
            return response()->json([
                'status' => 'error',
                'message' => 'Role not found.',
                'data' => null,
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'role_name' => [
                'required',
                'string',
                'max:255',
                'unique:roles,name,'.$role->id,
            ],

            'permissions' => [
                'required',
                'array',
                'min:1',
            ],

            'permissions.*' => [
                'required',
                'integer',
                'exists:permissions,id',
            ],
        ], [
            'role_name.required' => 'Role name is required.',
            'role_name.unique' => 'This role already exists.',

            'permissions.required' => 'Please select at least one permission.',
            'permissions.min' => 'Please select at least one permission.',
            'permissions.*.exists' => 'One or more selected permissions are invalid.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Please correct the validation errors.',
                'data' => [
                    'errors' => $validator->errors(),
                ],
            ], 422);
        }

        $validated = $validator->validated();

        try {

            DB::beginTransaction();

            $role->update([
                'name' => $validated['role_name'],
            ]);

            $permissions = Permission::whereIn(
                'id',
                $validated['permissions']
            )->get();

            $role->syncPermissions($permissions);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Role updated successfully.',
                'data' => [
                    'id' => $role->id,
                ],
            ], 200);
        } catch (\Throwable $e) {

            DB::rollBack();

            report($e);

            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong while updating the role.',
                'data' => null,
            ], 500);
        }
    }

    public function destroy(int $id)
    {
        $role = Role::find($id);

        if (! $role) {
            return response()->json([
                'status' => 'error',
                'message' => 'Role not found.',
                'data' => null,
            ], 404);
        }

        $role->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Role deleted successfully.',
            'data' => null,
        ], 200);
    }
}
