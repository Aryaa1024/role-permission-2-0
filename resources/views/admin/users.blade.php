@extends('admin.layouts.app')

@push('page-style')
@endpush

@section('page-content')
    <div class="row">
        <div class="col-sm-12">
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Manage Users</li>
            </ul>
            <hr class="mb-4">
        </div>
    </div>
    <div class="row mb-3 align-items-center">
        <div class="col-md-6">
            <h3 class="h3 mb-0">Manage Users</h3>
        </div>
        <div class="col-md-6 text-end">
            @can('users.import')
                <button type="button" class="btn btn-sm btn-outline-success m-1" id="importBtn"><i
                        class="bi bi-file-earmark-spreadsheet"></i>
                    Import</button>
            @endcan
            @can('users.create')
                <button type="button" class="btn btn-sm btn-outline-success m-1" id="addBtn"><i class="bi bi-plus"></i>
                    Add</button>
            @endcan
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <table class="table table-bordered table-hover table-striped" id="dataTable">
                <thead>
                    <tr>
                        <th>S.No.</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Mobile</th>
                        <th>Actions</th>
                    </tr>
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

    <div class="modal fade" id="commonModal" tabindex="-1" aria-labelledby="commonModalLabel" aria-hidden="true"
        data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="commonModalLabel">Add User</h5>
                    <button type="button" class="btn-close" id="closeModalBtn" aria-label="Close"></button>
                </div>
                <form id="commonForm" novalidate>
                    <input type="hidden" id="user_id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="role_id" class="form-label">Role <span class="text-danger">*</span></label>
                                <select name="role_id" id="role_id" class="form-select">
                                    <option value="">Select role</option>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name"
                                    placeholder="Enter name">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email"
                                    placeholder="Enter email">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="mobile_number" class="form-label">Mobile <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <select name="mobile_code" id="mobile_code" class="form-select">
                                        <option value="+91">+91</option>
                                    </select>
                                    <input type="tel" class="form-control" id="mobile_number" name="mobile_number"
                                        inputmode="numeric" maxlength="15" placeholder="Enter mobile number">
                                </div>
                                <div id="mobileError" class="invalid-feedback d-none"></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-sm btn-success" id="submitBtn"><i class="bi bi-check-lg"></i>
                            Save User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true"
        data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">Import User</h5>
                    <button type="button" class="btn-close" id="closeImportModalBtn" aria-label="Close"></button>
                </div>
                <form id="importForm" novalidate>
                    <div class="modal-body">
                        <div class="row">
                            {{-- Import Form --}}
                            <div class="col-sm-12 mb-2">
                                <span>Download: </span>
                                <a href="{{ asset('storage/sample_csv/users.csv') }}" class="fs-6" download>
                                    Sample CSV
                                </a>
                            </div>
                            <div class="col-sm-12 mb-2">
                                <label for="importFile">Import File: </label>
                                <input type="file" id="importFile" name="importFile" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-sm btn-success" id="submitBtn"><i
                                class="bi bi-check-lg"></i>
                            Import User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('page-script')
    <script>
        $(document).ready(function() {
            const form = $('#commonForm');
            const modal = $('#commonModal');
            const submitButton = $('#submitBtn');
            const mobileFields = $('#mobile_code, #mobile_number');

            let canExport = @json(auth()->user()->can('users.export'));
            let tableHeader = '<"row mb-3"<"col-sm-12 col-md-6"><"col-sm-12 col-md-6"f>>';

            if (canExport) {
                tableHeader = '<"row mb-3"<"col-sm-12 col-md-6"B><"col-sm-12 col-md-6"f>>';
            }


            const exportTitle = 'Manage Users';
            const exportOptions = {
                columns: [0, 1, 2, 3]
            };
            const table = $('#dataTable').DataTable({
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
                ajax: "{{ route('users.index') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'name'
                    },
                    {
                        data: 'email'
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: (_, __, row) => `${row.mobile_code} ${row.mobile_number}`
                    },
                    {
                        data: 'actions',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            const validator = form.validate({
                errorElement: 'div',
                errorClass: 'invalid-feedback',
                groups: {
                    mobile: 'mobile_code mobile_number'
                },
                rules: {
                    role_id: {
                        required: true
                    },
                    name: {
                        required: true,
                        minlength: 3,
                        maxlength: 50
                    },
                    email: {
                        required: true,
                        email: true,
                        maxlength: 255
                    },
                    mobile_code: {
                        required: true
                    },
                    mobile_number: {
                        required: true,
                        digits: true,
                        minlength: 4,
                        maxlength: 15
                    }
                },
                messages: {
                    role_id: {
                        required: 'Please select a role.'
                    },
                    name: {
                        required: 'Please enter name.',
                        minlength: 'Name must be at least 3 characters.',
                        maxlength: 'Name cannot exceed 50 characters.'
                    },
                    email: {
                        required: 'Please enter email.',
                        email: 'Please enter a valid email address.',
                        maxlength: 'Email cannot exceed 255 characters.'
                    },
                    mobile_code: {
                        required: 'Please select a mobile code.'
                    },
                    mobile_number: {
                        required: 'Please enter mobile number.',
                        digits: 'Please enter digits only.',
                        minlength: 'Please enter a valid mobile number.',
                        maxlength: 'Please enter a valid mobile number.'
                    }
                },
                errorPlacement(error, element) {
                    if (['mobile_code', 'mobile_number'].includes(element.attr('name'))) {
                        error.insertAfter(element.closest('.input-group'));
                        return;
                    }

                    error.insertAfter(element);
                },
                highlight(element) {
                    $(element).addClass('is-invalid');
                },
                unhighlight(element) {
                    $(element).removeClass('is-invalid');
                },
                submitHandler(formElement) {
                    const userId = $('#user_id').val();
                    const isEdit = Boolean(userId);
                    const url = isEdit ? "{{ route('users.update', ':id') }}".replace(':id', userId) :
                        "{{ route('users.store') }}";
                    const label = isEdit ? 'Update User' : 'Save User';
                    const formData = new FormData(formElement);

                    formData.append('_token', '{{ csrf_token() }}');
                    clearServerErrors();
                    submitButton.prop('disabled', true).html(
                        '<span class="spinner-border spinner-border-sm me-1"></span> Saving...');

                    $.ajax({
                        url,
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success(response) {
                            if (response.status !== 'success') {
                                alert(response.message || 'Something went wrong.');
                                return;
                            }

                            modal.modal('hide');
                            table.ajax.reload(null, false);
                            alert(response.message);
                        },
                        error(xhr) {
                            if (xhr.status !== 422) {
                                alert(xhr.responseJSON?.message || 'Something went wrong.');
                                return;
                            }

                            const errors = xhr.responseJSON?.errors || xhr.responseJSON?.data
                                ?.errors || {};

                            Object.entries(errors).forEach(([field, messages]) => showServerError(
                                field, messages[0]));
                        },
                        complete() {
                            submitButton.prop('disabled', false).html(
                                `<i class="bi bi-check-lg"></i> ${label}`);
                        }
                    });

                    return false;
                }
            });

            $('#importBtn').on('click', function() {
                $('#importModalLabel').text('Import User');
                $('#importModal').modal('show');
            });
            $('#closeImportModalBtn').on('click', function() {
                form[0].reset();
                $('#importModal').modal('hide');
            });

            $('#addBtn').on('click', () => openModal());
            $('#closeModalBtn').on('click', () => modal.modal('hide'));
            modal.on('hidden.bs.modal', resetForm);

            $('#mobile_number').on('input', function() {
                this.value = this.value.replace(/\D/g, '');
                clearMobileError();
            });

            $(document).on('click', '.editBtn', function() {
                const button = $(this);

                button.prop('disabled', true);
                resetForm();
                $.get("{{ route('users.edit', ':id') }}".replace(':id', button.data('id')))
                    .done(response => {
                        const user = response.data;
                        $('#user_id').val(user.id);
                        $('#role_id').val(user.role_id);
                        $('#name').val(user.name);
                        $('#email').val(user.email);
                        $('#mobile_code').val(user.mobile_code);
                        $('#mobile_number').val(user.mobile_number);
                        $('#commonModalLabel').text('Edit User');
                        submitButton.html('<i class="bi bi-check-lg"></i> Update User');
                        modal.modal('show');
                    })
                    .fail(xhr => alert(xhr.responseJSON?.message || 'Unable to fetch user.'))
                    .always(() => button.prop('disabled', false));
            });

            $(document).on('click', '.deleteBtn', function() {
                const button = $(this);

                if (!confirm('Are you sure you want to delete this user?')) {
                    return;
                }

                const formData = new FormData();

                formData.append('_token', '{{ csrf_token() }}');
                button.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm"></span>');
                $.ajax({
                    url: "{{ route('users.delete', ':id') }}".replace(':id', button.data('id')),
                    type: 'DELETE',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success(response) {
                        if (response.status !== 'success') {
                            alert(response.message || 'Unable to delete user.');
                            return;
                        }

                        table.ajax.reload(null, false);
                        alert(response.message);
                    },
                    error(xhr) {
                        alert(xhr.responseJSON?.message || 'Unable to delete user.');
                    },
                    complete() {
                        button.prop('disabled', false).html('<i class="bi bi-trash"></i>');
                    }
                });
            });

            function openModal() {
                resetForm();
                $('#commonModalLabel').text('Add User');
                submitButton.html('<i class="bi bi-check-lg"></i> Save User');
                modal.modal('show');
            }

            function resetForm() {
                form[0].reset();
                $('#user_id').val('');
                validator.resetForm();
                form.find('.is-invalid').removeClass('is-invalid');
                clearServerErrors();
            }

            function showServerError(field, message) {
                if (['mobile_code', 'mobile_number'].includes(field)) {
                    mobileFields.addClass('is-invalid');
                    $('#mobileError').text(message).removeClass('d-none');
                    return;
                }

                const input = $('#' + field);
                input.addClass('is-invalid');
                input.next('.invalid-feedback').remove();
                $('<div class="invalid-feedback"></div>').text(message).insertAfter(input);
            }

            function clearMobileError() {
                mobileFields.removeClass('is-invalid');
                $('#mobileError').addClass('d-none').text('');
            }

            function clearServerErrors() {
                form.find('.invalid-feedback:not(#mobileError)').remove();
                clearMobileError();
            }
        });
    </script>
@endpush
