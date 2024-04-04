@extends('layouts.main')
@section('content')
<div class="my-3">
    <h1 class="text-center"> Chart</h1>
    <div class="row">
        <div class="col-2"></div>
        <div class="form-group my-2 col-4">
            <p>Month</p>
            <select name="month" id="month" class="form-control">
                <option hidden >Select Month</option>
                @foreach ($monthsName as $key => $value)
                <option value="{{$key}}" id="{{$key}}">{{$value}}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group my-2  col-4" id='yearBox'>
        <p>Year</p>
        <select name="year" id="year" class="form-control">
            <option hidden>Select Year</option>
            @foreach ($years as $year)
            <option value="{{$year}}" id="{{$year}}">{{$year}}</option>
            @endforeach
        </select>
    </div>
    <div class="col-2"></div>
</div>
    

    <div class="container-fluid mx-auto">
        <canvas id="myChart"></canvas>
    </div>
    {{-- Modal --}}
    <div class="modal" id="myModal2">
        <div class="modal-dialog">
            <div class="modal-content">
        
                <!-- Modal Header -->
                <div class="modal-header justify-content-center">
                    <h4 class="modal-title" id='modalTitle'></h4>
                </div>
                <!-- Modal Body -->
                <div class="modal-body">
                    <div class="container-fluid mx-auto">
                        <canvas id="subChart"></canvas>
                    </div>
                </div>
                        
                <!-- Modal footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger cls-btn">Close</button>
                </div>
        
            </div>
        </div>
</div>
{{-- Modal --}}

</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    
    (function($){
        $(document).ready(function () {
            let month = null, year = null;
            $('#yearBox').hide();
            $('#month').change(function (e) { 
                $('#yearBox').show();
                month = $(this).val();

                if(year){
                    loadData(month, year);
                }
            });
            $('#year').change(function (e) { 
                year = $(this).val();
                loadData(month, year);
            });
            function loadData(month, year){
                $.ajax({
                    type: "GET",
                    url: "{{ route('getmonthyeardata') }}",
                    data: {month, year},
                    success: function (response) {
                        createChart(response.data)
                    }
                });
            }
            function createChart(data){
                const canvas = document.getElementById('myChart'); // Replace 'chart' with your canvas ID
                const ctx = canvas.getContext('2d');
                let chartStatus = Chart.getChart("myChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                let labels = [];
                let values = [];
                for (const key in data) {
                    labels.push(key);
                    values.push(data[key])              
                }
                let newChart = new Chart(ctx, {
                    type:'bar',
                    data:{
                        labels: labels,
                        datasets: [
                            {
                                label: 'Blog Published',
                                data:values,
                            }
                        ]
                    }
                });
            function barSelect(evt){
                        const res = newChart.getElementsAtEventForMode(evt, 'nearest', { intersect: true }, true);
                        if (res.length === 0) return; // No bar clicked
                        let date = newChart.data.labels[res[0].index];
                        $('#modalTitle').text('Pie Chart Of Date ' + date);
                        $.ajax({
                            type: "GET",
                            url: "{{route('getdatechart')}}",
                            data:{date},
                            success: function (response) {
                                loadPieChart(response.data)
                            }
                        });
                        
                }
                canvas.onclick = barSelect;
            function loadPieChart(data) {
                    $('#myModal2').show();
                    const canvas = document.getElementById('subChart');
                    const ctx = canvas.getContext('2d');
                    let chartStatus = Chart.getChart("subChart"); // <canvas> id
                    if (chartStatus != undefined) {
                        chartStatus.destroy();
                    }
                let labels = [];
                let values = [];
                for (const key in data) {
                    labels.push(key);
                    values.push(data[key])              
                }
                    let newChart = new Chart(ctx, {
                        type:'pie',
                        data:{
                            labels: labels,
                            datasets: [
                                {
                                    data:values,
                                }
                            ]
                        }
                    });

            }
            $('.cls-btn').click(function () { 
                $('#myModal2').hide();
            })
            }
            
        }); 
    })(jQuery);
</script>

@endsection