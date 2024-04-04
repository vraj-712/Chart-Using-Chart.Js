@extends('layouts.main')
@section('content')
<div class="my-3">
    <h1 class="text-center">Month Chart</h1>
    <div class="form-group container my-2">
        <select name="year" id="year" class="form-control">
            <option value="{{null}}">Select Year</option>
            @foreach ($years as $year)
                <option value="{{$year}}" id="{{$year}}">{{$year}}</option>
            @endforeach
        </select>
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
                        <h4 class="modal-title" >Pie Chart</h4>
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
            let year = null, sport = null;
            $('#year').change(function (e) { 
                year = $(this).val();
                if(!year){
                    year = null;
                }
                console.log($(this).val());
                loadData();
            });
            $('#sport').change(function (e) { 
                sport = $(this).val();
                if(!sport){
                    sport = null;
                }
                console.log($(this).val());
                loadData();
            });
            loadData();
            function loadData(){
                $.ajax({
                    type: "GET",
                    url: "{{route('getmonthvise')}}",
                    data: {year, sport},
                    success: function (response) {
                        console.log(response)
                        createChart(response);
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

                let newChart = new Chart(ctx, {
                    data:{
                        labels: data.labels,
                        datasets: [
                            {
                                type:'bar',
                                label:sport ?? "All Sport Data",
                                data:data.value,
                            }
                        ]
                    }
                });
                
                function barSelect(evt){
                        const res = newChart.getElementsAtEventForMode(evt, 'nearest', { intersect: true }, true);
                        if (res.length === 0) return; // No bar clicked
                        let monthName = newChart.data.labels[res[0].index];
                        // console.log(monthName)
                        $.ajax({
                            type: "GET",
                            url: "{{route('getspecificmonthvise')}}",
                            data: {month:monthName,year},
                            success: function (response) {
                                loadPieChart(response);
                            }
                        });
                        
                }
            canvas.onclick = barSelect;

            function loadPieChart(data) {
                $('#myModal2').show();
                console.log(data.labels);
                const canvas = document.getElementById('subChart');
                const ctx = canvas.getContext('2d');
                let chartStatus = Chart.getChart("subChart"); // <canvas> id
                if (chartStatus != undefined) {
                    chartStatus.destroy();
                }
                let newChart = new Chart(ctx, {
                    type:'pie',
                    data:{
                        labels: data.labels,
                        datasets: [
                            {
                                data:data.values,
                            }
                        ]
                    }
                });

            }
            $('.cls-btn').click(function () { 
                $('#myModal2').hide();
            });
            
            }
        }); 
    })(jQuery);
</script>

@endsection