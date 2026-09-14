<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\DataTables;

class UserController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::of(User::query())
                ->addIndexColumn()
                ->addColumn('actions', fn (User $user): string => $this->actionButtons($user))
                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('admin.users', ['roles' => Role::orderBy('name')->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validatedData($request);

        $user = DB::transaction(function () use ($data): User {
            $user = User::create($this->userAttributes($data));
            $user->assignRole(Role::findOrFail($data['role_id']));

            return $user;
        });

        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully.',
            'data' => ['id' => $user->id],
        ], 201);
    }

    public function edit(int $id): JsonResponse
    {
        $user = User::with('roles')->find($id);

        if (! $user) {
            return $this->notFound();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'User fetched successfully.',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'mobile_code' => $user->mobile_code,
                'mobile_number' => $user->mobile_number,
                'role_id' => $user->roles->first()?->id,
            ],
        ]);
    }
    public function update(Request $request, int $id): JsonResponse
    {
        $user = User::find($id);

        if (! $user) {
            return $this->notFound();
        }

        $data = $this->validatedData($request, $user);

        DB::transaction(function () use ($user, $data): void {
            $user->update($this->userAttributes($data));
            $user->syncRoles([Role::findOrFail($data['role_id'])]);
        });

        return response()->json([
            'status' => 'success',
            'message' => 'User updated successfully.',
            'data' => ['id' => $user->id],
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $user = User::find($id);

        if (! $user) {
            return $this->notFound();
        }

        $user->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'User deleted successfully.',
            'data' => null,
        ]);
    }

    private function validatedData(Request $request, ?User $user = null): array
    {
        return $request->validate([
            'role_id' => ['required', 'exists:roles,id'],
            'name' => ['required', 'string', 'min:3', 'max:50'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'mobile_code' => ['required', 'regex:/^\+\d{1,4}$/'],
            'mobile_number' => [
                'required',
                'digits_between:4,15',
                Rule::unique('users', 'mobile_number')->where('mobile_code', $request->input('mobile_code'))->ignore($user),
            ],
        ], [
            'role_id.required' => 'Please select a role.',
            'role_id.exists' => 'Selected role is invalid.',
            'name.required' => 'Please enter name.',
            'name.min' => 'Name must be at least 3 characters.',
            'email.required' => 'Please enter email.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email is already registered.',
            'mobile_code.required' => 'Please select a mobile code.',
            'mobile_code.regex' => 'Please select a valid mobile code.',
            'mobile_number.required' => 'Please enter mobile number.',
            'mobile_number.digits_between' => 'Please enter a valid mobile number.',
            'mobile_number.unique' => 'This mobile number is already registered for the selected code.',
        ]);
    }

    private function userAttributes(array $data): array
    {
        return [
            'name' => trim($data['name']),
            'email' => strtolower(trim($data['email'])),
            'mobile_code' => $data['mobile_code'],
            'mobile_number' => preg_replace('/\D/', '', $data['mobile_number']),
        ];
    }

    private function notFound(): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => 'User not found.',
            'data' => null,
        ], 404);
    }

    private function actionButtons(User $user): string
    {
        return '<div class="d-flex align-items-center gap-2"><button type="button" class="btn btn-sm btn-outline-primary editBtn" data-id="'.$user->id.'" title="Edit"><i class="bi bi-pencil"></i></button><button type="button" class="btn btn-sm btn-outline-danger deleteBtn" data-id="'.$user->id.'" title="Delete"><i class="bi bi-trash"></i></button></div>';
    }
}
