@extends('layouts.main')
@section('content')
<div class="my-3">

    <h1 class="text-center">Year Chart</h1>
    <div class="container-fluid mx-auto">
        <canvas id="myChart"></canvas>
    </div>

</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  
<script>
    
    (function($){
        $(document).ready(function () {
            $.ajax({
                type: "GET",
                url: "{{route('getyearvise')}}",
                success: function (response) {
                    console.log(response)
                    createChart(response.data, response.label);
                }
            });
            function createChart(datas, labels){
                let ctx = $('#myChart')
              let chartStatus = Chart.getChart("myChart"); // <canvas> id
              if (chartStatus != undefined) {
              chartStatus.destroy();
              }
              let datasets = [];

              for (const key in datas) {
                datasets.push({
                    type: 'line',
                    label: key,
                    data: datas[key]
                })
              }
              new Chart(ctx, {
                data:{
                    labels:labels,
                    datasets:datasets,
                },
                options:{
                      scales:{
                          y:{
                              beginAtZero:true,
                          }
                      }
                  },
              })
            }
        });
    })(jQuery);
</script>

    
@endsection