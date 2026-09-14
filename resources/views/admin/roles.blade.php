@extends('admin.layouts.app')
@push('page-style')
    <style>

    </style>
@endpush
@section('page-content')
    <div class="row">
        <div class="col-sm-12">
            <ul class="breadcrumb">
                <li class="breadcrumb-item"> <a href="{{ route('admin.dashboard') }}">Dashboard</a> </li>
                <li class="breadcrumb-item active">Roles & Permissions</li>
            </ul>
            <hr class="mb-4">
        </div>
    </div>
    <div class="row mb-3 d-flex align-items-center justify-content-between">
        <div class="col-sm-12 col-md-6">
            <h3 class="h3">Roles & Permissions</h3>
        </div>
        <div class="col-sm-12 col-md-6 text-end">
            @can('roles.create')
                <button class="btn btn-sm btn-outline-success" id="addBtn"><i class="i bi-plus"></i> Add</button>
            @endcan
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-hover table-striped" id="dataTable">
                        <thead>
                            <th>S.No.</th>
                            <th>Role Name</th>
                            <th>Assigned Permissions</th>
                            <th>Actions</th>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                <div class="card-arrow">
                    <div class="card-arrow-top-left"></div>
                    <div class="card-arrow-top-right"></div>
                    <div class="card-arrow-bottom-left"></div>
                    <div class="card-arrow-bottom-right"></div>
                </div>
            </div>
        </div>
    </div>
    {{-- Modal Form --}}
    <div class="modal fade" id="commonModal" tabindex="-1" aria-labelledby="commonModalLabel" aria-hidden="true"
        data-bs-backdrop="static" data-bs-keyboard="false">

        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="modal-title" id="commonModalLabel">
                            Add Role
                        </h5>
                    </div>
                    <i class="bi bi-x-lg cursor-pointer p-1" id="closeModalBtn"></i>
                </div>

                <form id="commonForm" novalidate>

                    <input type="hidden" id="role_id" name="role_id">

                    <div class="modal-body">
                        {{-- Role Name --}}
                        <div class="mb-3">
                            <label for="role_name" class="form-label">
                                Role Name <span class="text-danger">*</span>
                            </label>

                            <input type="text" class="form-control" id="role_name" name="role_name"
                                placeholder="Enter role name">
                        </div>
                        {{-- Permissions --}}
                        <div class="mb-3">
                            <label for="role_name" class="form-label">
                                Assign Permissions <span class="text-danger">*</span>
                            </label>
                            <div id="permissionError" class="text-danger small mb-2"></div>
                            <div class="row">
                                @foreach ($permissionGroups as $module => $permissions)
                                    <div class="col-sm-12 col-md-6 col-lg-4 mb-3">
                                        <div class="card">
                                            <div class="card-header">
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input moduleSelectAll"
                                                        data-module="{{ $module }}" id="module_{{ $module }}">

                                                    <label class="form-check-label fw-semibold"
                                                        for="module_{{ $module }}">
                                                        {{ ucfirst($module) }}
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="card-body">

                                                @foreach ($permissions as $permission)
                                                    <div class="form-check mb-2">

                                                        <input type="checkbox"
                                                            class="form-check-input permission-checkbox module-{{ $module }}"
                                                            name="permissions[]" value="{{ $permission['id'] }}"
                                                            id="permission_{{ $permission['id'] }}">

                                                        <label class="form-check-label"
                                                            for="permission_{{ $permission['id'] }}">
                                                            {{ ucfirst($permission['permission']) }}
                                                        </label>

                                                    </div>
                                                @endforeach

                                            </div>
                                            <div class="card-arrow">
                                                <div class="card-arrow-top-left"></div>
                                                <div class="card-arrow-top-right"></div>
                                                <div class="card-arrow-bottom-left"></div>
                                                <div class="card-arrow-bottom-right"></div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-sm btn-success" id="submitBtn">
                            <i class="bi bi-check-lg"></i>
                            Save Role
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('page-script')
    <script>
        $(document).ready(function() {

            let canExport = @json(auth()->user()->can('roles.export'));
            let tableHeader = '<"row mb-3"<"col-sm-12 col-md-6"><"col-sm-12 col-md-6"f>>';

            if (canExport) {
                tableHeader = '<"row mb-3"<"col-sm-12 col-md-6"B><"col-sm-12 col-md-6"f>>';
            }
            const exportTitle = 'Manage Roles';
            const exportOptions = {
                columns: [0, 1, 2]
            };
            let table = $('#dataTable').DataTable({
                serverSide: true,
                dom: tableHeader +
                    '<"row mb-2"<"col-sm-12"l>>' +
                    '<"row mb-2"<"col-sm-12 table-responsive"t>>' +
                    '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                buttons: [{
                        extend: 'csv',
                        text: '<i class="bi bi-file-earmark-spreadsheet"></i> CSV',
                        className: 'btn btn-sm btn-outline-primary m-1',
                        'title': exportTitle,
                        exportOptions
                    },
                    {
                        extend: 'excel',
                        text: '<i class="bi bi-file-earmark-excel"></i> Excel',
                        className: 'btn btn-sm btn-outline-success m-1',
                        'title': exportTitle,
                        exportOptions
                    },
                    {
                        extend: 'pdf',
                        text: '<i class="bi bi-file-earmark-pdf"></i> PDF',
                        className: 'btn btn-sm btn-outline-danger m-1',
                        'title': exportTitle,
                        exportOptions
                    },
                    {
                        extend: 'print',
                        text: '<i class="bi bi-printer"></i> Print',
                        className: 'btn btn-sm btn-outline-secondary m-1',
                        'title': exportTitle,
                        exportOptions
                    },
                ],
                ajax: "{{ route('roles.index') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        'orderable': false,
                        'searchable': false
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'permissions',
                        name: 'permissions',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    },
                ]
            });
            $(document).on('click', '#addBtn', function() {
                resetForm();
                $('#commonModalLabel').text('Add Role');
                $('#submitBtn').html(`
                    <i class="bi bi-check-lg"></i>
                    Save Role
                `);
                $('#commonModal').modal('show');
            });

            $(document).on('click', '#closeModalBtn', function() {
                resetForm();
                $('#commonModal').modal('hide');
            });

            $(document).on('change', '.moduleSelectAll', function() {

                let module = $(this).data('module');
                let checked = $(this).is(':checked');

                $('.module-' + module).prop('checked', checked);

            });

            $(document).on('change', '.permission-checkbox', function() {
                let classes = $(this).attr('class').split(/\s+/);
                let moduleClass = classes.find(function(className) {
                    return className.startsWith('module-');
                });
                if (!moduleClass) {
                    return;
                }
                let module = moduleClass.replace('module-', '');
                let permissions = $('.module-' + module);
                let allChecked =
                    permissions.length === permissions.filter(':checked').length;
                $('#module_' + module).prop('checked', allChecked);
            });

            $('#commonForm').validate({
                ignore: [],
                rules: {
                    role_name: {
                        required: true,
                        minlength: 2,
                        maxlength: 50
                    },
                    'permissions[]': {
                        required: true
                    }
                },
                messages: {
                    role_name: {
                        required: 'Please enter role name.',
                        minlength: 'Role name must be at least 2 characters.',
                        maxlength: 'Role name cannot exceed 50 characters.'
                    },
                    'permissions[]': {
                        required: 'Please select at least one permission.'
                    }
                },
                highlight: function(element) {
                    $(element).addClass('is-invalid');
                },
                unhighlight: function(element) {
                    $(element).removeClass('is-invalid');
                },
                errorPlacement: function(error, element) {
                    if (element.attr('name') === 'role_name') {
                        error.insertAfter(element);
                        return;
                    }
                    if (element.attr('name') === 'permissions[]') {
                        $('#permissionError')
                            .html(error);
                        return;
                    }
                    error.insertAfter(element);
                },
                submitHandler: function(form) {
                    const roleId = $('#role_id').val();
                    const isEdit = Boolean(roleId);
                    const url = isEdit ?
                        "{{ route('roles.update', ':id') }}".replace(':id', roleId) :
                        "{{ route('roles.store') }}";
                    const button = $('#submitBtn');
                    const label = isEdit ? 'Update Role' : 'Save Role';
                    const formData = new FormData(form);

                    formData.append('_token', '{{ csrf_token() }}');

                    $('#permissionError').empty();
                    button.prop('disabled', true).html(
                        '<span class="spinner-border spinner-border-sm me-1"></span> Saving...'
                    );

                    $.ajax({
                        url: url,
                        type: isEdit ? 'PUT' : 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            if (response.status !== 'success') {
                                alert(response.message || 'Something went wrong.');
                                return;
                            }

                            $('#commonModal').modal('hide');
                            resetForm();
                            table.ajax.reload(null, false);
                            alert(response.message);
                        },
                        error: function(xhr) {
                            if (xhr.status !== 422) {
                                alert(xhr.responseJSON?.message || 'Something went wrong.');
                                return;
                            }

                            const errors = xhr.responseJSON?.data?.errors || xhr
                                .responseJSON?.errors || {};
                            const validator = $('#commonForm').validate();

                            Object.entries(errors).forEach(function([field, messages]) {
                                const message = messages[0];

                                if (field === 'permissions' || field.startsWith(
                                        'permissions.')) {
                                    $('#permissionError').text(message);
                                    $('.permission-checkbox').addClass(
                                        'is-invalid');
                                    return;
                                }

                                $('#' + field).addClass('is-invalid');
                                validator.showErrors({
                                    [field]: message
                                });
                            });
                        },
                        complete: function() {
                            button.prop('disabled', false).html(
                                `<i class="bi bi-check-lg"></i> ${label}`
                            );
                        }
                    });

                    return false;
                }
            });

            $(document).on('click', '.editBtn', function() {
                const button = $(this);
                const roleId = button.data('id');

                resetForm();
                button.prop('disabled', true);
                $.ajax({
                    url: "{{ route('roles.edit', ':id') }}".replace(':id', roleId),
                    type: 'GET',
                    success: function(res) {
                        if (res.status !== 'success') {
                            alert(res.message || 'Unable to fetch role.');
                            return;
                        }

                        $('#role_id').val(res.data.id);
                        $('#role_name').val(res.data.name);
                        res.data.permissions.forEach(function(permissionId) {
                            $('#permission_' + permissionId).prop('checked', true);
                        });
                        $('.moduleSelectAll').each(function() {
                            const permissions = $('.module-' + $(this).data('module'));
                            $(this).prop('checked', permissions.length === permissions
                                .filter(':checked').length);
                        });
                        $('#commonModalLabel').text('Edit Role');
                        $('#submitBtn').html('<i class="bi bi-check-lg"></i> Update Role');
                        $('#commonModal').modal('show');
                    },
                    error: function(xhr) {
                        alert(xhr.responseJSON?.message || 'Unable to fetch role.');
                    },
                    complete: function() {
                        button.prop('disabled', false);
                    }
                });
            });

            $(document).on('click', '.deleteBtn', function() {
                const button = $(this);
                const roleId = button.data('id');

                if (!confirm('Are you sure you want to delete this role?')) {
                    return;
                }

                const formData = new FormData();

                formData.append('_token', '{{ csrf_token() }}');
                button.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm"></span>'
                );

                $.ajax({
                    url: "{{ route('roles.delete', ':id') }}".replace(':id', roleId),
                    type: 'DELETE',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        if (res.status !== 'success') {
                            alert(res.message || 'Unable to delete role.');
                            return;
                        }

                        table.ajax.reload(null, false);
                        alert(res.message);
                    },
                    error: function(xhr) {
                        alert(xhr.responseJSON?.message || 'Unable to delete role.');
                    },
                    complete: function() {
                        button.prop('disabled', false).html('<i class="bi bi-trash"></i>');
                    }
                });
            });

            function resetForm() {
                $('#commonForm')[0].reset();
                $('#role_id').val('');
                $('#permissionError').empty();
                $('#commonForm').validate().resetForm();
                $('#commonForm .is-invalid').removeClass('is-invalid');
            }

        });
    </script>
@endpush
