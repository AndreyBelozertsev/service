<div class="container">
    <div class="row">
        <form method="POST" action="{{route('admin.get-feedback') }}">
            @csrf
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="inputDate1">Начало</label>
                    <input type="date" class="form-control" name="date1" required  id="inputDate1" placeholder="Дата начала" value="{{ old('date1') }}">
                    @error('date1')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group col-md-6">
                    <label for="inputDate2">Окончание</label>
                    <input type="date" class="form-control" name="date2" required  id="inputDate2" placeholder="Дата окончания" value="{{ old('date2') }}">
                    @error('date2')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <button type="submit" class="btn btn-primary my-1">Сформировать таблицу</button>
        </form>
    </div>
</div>