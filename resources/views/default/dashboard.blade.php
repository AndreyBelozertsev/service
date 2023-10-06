@push('block.content')

<div class="container">
    <div class="row">
        @if($clients->isNotEmpty())
            <form method="GET" action="{{route('admin.get-statistics') }}">
                
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="inputDate1">Дата начала</label>
                        <input type="date" class="form-control" name="date1" required  id="inputDate1" placeholder="Дата начала" value="{{ old('date1') }}">
                        @error('date1')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group col-md-6">
                        <label for="inputDate2">Дата окончания</label>
                        <input type="date" class="form-control" name="date2" required  id="inputDate2" placeholder="Дата окончания" value="{{ old('date2') }}">
                        @error('date2')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="form-group">
                    <label class="my-1 mr-2" for="inlineFormCustomSelectPref">Организация</label>
                    <select name="counter_number" class="custom-select my-1 mr-sm-2">
                        @foreach($clients as $client)
                            <option value="{{ $client->counter_number }}">{{ $client->name }} {{ $client->counter_number }} - ({{ config("constant.clients_type.$client->type")}} )</option>
                        @endforeach
                    </select>
                    @error('counter_number')
                            <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary my-1">Получить отчет</button>
            </form>
        @endif
    </div>
</div>

@endpush