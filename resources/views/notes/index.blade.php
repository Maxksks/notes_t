@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Все заметки</div>
                    <div class="card-body">
                    <a class="btn btn-success mb-3" id="createNewNote"
                        href="javascript:void(0)">Создать заметку</a>
                    <table class="data-table table table-striped">
                        <thead>
                            <tr>
                                <th scope="col">id</th>
                                <th scope="col">Название</th>
                                <th scope="col">Описание</th>
                                <th scope="col">Дата создания</th>
                                <th scope="col"></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>

                    <div class="modal fade" id="ajaxModel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title" id="modelHeading"></h4>
                                    <button type="button" class="close"
                                            data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">

                                    <form id="noteForm" name="noteForm" class="form-horizontal">
                                        <input type="hidden" name="id" id="id">

                                        <div class="form-group">
                                            <label for="title" class="col-sm-3 control-label">Заголовок</label>
                                            <div class="col-sm-12">
                                                <input type="text" class="form-control"
                                                        id="title" name="title" maxlength="50" required>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="title" class="col-sm-3 control-label">Описание</label>
                                            <div class="col-sm-12">
                                                <textarea name="description" id="description"
                                                        class="form-control" rows="4" required></textarea>
                                            </div>
                                        </div>

                                        <div class="col-sm-offset-2 col-sm-10">
                                            <button type="submit" class="btn btn-primary"
                                                    id="saveBtn" value="create">Сохранить</button>
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>

                                        </div>

                                    </form>
                                </div>
                            </div>

                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        $(function(){
            var table = $('.data-table').DataTable({
                language:{
                    processing: "Подождите...",
                    search: "Поиск:",
                    lengthMenu: "Показать _MENU_ записей",
                    info: "Записи с _START_ до _END_ из _TOTAL_ записей",
                    infoEmpty: "Записи с 0 до 0 из 0 записей",
                    infoFiltered: "(отфильтровано из _MAX_ записей)",
                    loadingRecords: "Загрузка записей...",
                    zeroRecords: "Записи отсутствуют.",
                    emptyTable: "В таблице отсутствуют данные",
                    paginate: {
                        first: "Первая",
                        previous: "Предыдущая",
                        next: "Следующая",
                        last: "Последняя"
                    },

                    aria: {
                        sortAscending: ": активировать для сортировки столбца по возрастанию",
                        sortDescending: ": активировать для сортировки столбца по убыванию"
                    }
                },
                processing:true,
                serverSide:true,

                ajax:{
                    url:"{{ route('notes.index') }}",
                    type: "GET"
                },
                columns:[
                    {data: 'id', name: 'index'},
                    {data: 'title', name: 'title'},
                    {data: 'description', name: 'description'},
                    {data: 'created_at', name: 'created_at'},
                    {data: 'action', name: 'action', orderblade: false, searchable: false},
                ],
                columnDefs:[
                    {width: '23%', targets: '3'},
                    {width: '13%', targets: '4'},
                ],
            });
        });

        var validator = $('#noteForm').validate({
            errorClass: 'is-invalid',
            rules: {
                title: {
                    required: true,
                    maxlength: 30
                },
                description: {
                    required: true,
                    maxlength: 100
                }
            },
            messages: {
                title: {
                    required: "Заголовок обязательно для заполнения",
                    maxlength: jQuery.validator.format("Допустимо максимум {0} символов"),
                },
                description: {
                    required: "Описание обязательно для заполнения",
                    maxlength: jQuery.validator.format("Допустимо максимум {0} символов"),
                },
            }
        });

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        function clearValidateInputs(){
            $('#noteForm .is-invalid').each(function(){
                $(this).removeClass("is-invalid");
            });
        }

        $("#createNewNote").click(function(){
            validator.resetForm()
            clearValidateInputs()

            $('#saveBtn').val("Создать заметку");
            $('#id').val('');
            $('#noteForm').trigger("reset");
            $('#modelHeading').html("Создать заметку");
            $('#ajaxModel').modal('show');
        });

        $('body').on('click', '.editNote', function(){
            validator.resetForm()
            clearValidateInputs()

            var id = $(this).data('id');

            $.get(`{{ route("notes.index") }}/${id}/edit`, function(note){
                $('#modelHeading').html("Редактировать");
                $('#saveBtn').val("edit-user");
                $('#ajaxModel').modal("show");
                $('#id').val(note.id);
                $('#title').val(note.title);
                $('#description').val(note.description);
            })
        });

        $('#saveBtn').click(function(e){
            $(this).html('Отправка...');

            $.ajax({
                data: $('#noteForm').serialize(),
                url: "{{ route('notes.store') }}",
                type: "POST",
                dataType: 'json',
                success: function(data){
                    $('#noteForm').trigger("reset");
                    $('#ajaxModel').modal('hide');
                    $('#saveBtn').html('Сохранить');
                    table.draw();
                    console.log(data);
                },
                error: function(data){
                    console.log('Error:', data);
                    $('#saveBtn').html('Сохранить');
                }
            });
        });

        $('body').on('click', '.deleteNote', function(){
            var id_ = $(this).data("id");

            $.ajax({
                url: `{{ route('notes.store') }}/${id_}`,
                type: "DELETE",
                data: {
                    _token: "{{ csrf_token() }}",
                    id: id_
                },
                success: function(data){
                    table.draw();
                    console.log(data);
                },
                error: function(data){
                    console.log('Error:', data);
                }
            });
        });

    </script>
@endpush
