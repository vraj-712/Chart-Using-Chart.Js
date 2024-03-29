@extends('layouts.main')
@section('content')
<div class="container my-3 ">
    <div class="container">
        <select name="author" id="author" class="form-control w-25">
            
        </select>
    </div>
    <div class="container">
    <div class="form-group d-inline-block mx-1 ">
        <label for="datefrom">From </label><br>
        <input type="date" name="datefrom" id="datefrom" class="form-control">
    </div>
    <div class="form-group d-inline-block mx-1">
        <label for="dateto">To </label><br>
        <input type="date" name="dateto" id="dateto" class="form-control">
    </div>
    </div>
    <div class="container my-2">
        <div class="form-group d-inline-block me-3 ms-1">
            <label for="bar">Bar</label>
            <input type="radio" name="charttype" id="bar" value="bar"  class="form-check-input" checked>
        </div>
        <div class="form-group d-inline-block mx-3">
            <label for="pie">Pie</label>
            <input type="radio" name="charttype" id="pie" value="pie" class="form-check-input" >
        </div>
        <div class="form-group d-inline-block mx-3">
            <label for="line">Line</label>
            <input type="radio" name="charttype" id="line" value='line' class="form-check-input" >
        </div>
        <div class="form-group d-inline-block mx-3">
            <label for="doughnut">Doughnut</label>
            <input type="radio" name="charttype" id="doughnut" value='doughnut' class="form-check-input" >
        </div>
        <div class="form-group d-inline-block mx-3">
            <label for="polarArea">PolarArea</label>
            <input type="radio" name="charttype" id="polarArea" value='polarArea' class="form-check-input" >
        </div>
        <div class="form-group d-inline-block mx-3">
            <label for="radar">Radar</label>
            <input type="radio" name="charttype" id="radar" value='radar' class="form-check-input" >
        </div>
    </div>
</div>
<div class="container w-75 mx-auto">
    <canvas id="myChart"></canvas>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  
<script>

  (function($){
      $(document).ready(function () {
          let from = null;
          let to = null,authorId = null;
          let fetchData;
          function loadData(){
              $.ajax({
                  type: "GET",
                  url: "{{route('categoryVise')}}",
                  data: {from, to, authorId},
                  success: function (response) {
                      console.log(response)
                      fetchData = response;
                      type = $("input[name='charttype']:checked").val();
                      createChart(response,type);
                  }
              });
          }
          fetchAuthor();
          loadData();
          function createChart(data,type){
              let ctx = $('#myChart')
              let chartStatus = Chart.getChart("myChart"); // <canvas> id
              if (chartStatus != undefined) {
              chartStatus.destroy();
              }
              newChart = new Chart(ctx,{
                  type:type,
                  data:{
                      labels: data.label,
                      datasets:[
                          {
                              label:'sport data',
                              data: data.value,
                              borderWidth:2,
                              xAxisKey: 'sport',
                              yAxisKey: 'total'
                          }
                      ]
                  },
                  options:{
                      scales:{
                          y:{
                              beginAtZero:true,
                          }
                      }
                  }

              })
              
          }
          $('input[name="charttype"]').change(function (e) { 
              e.preventDefault();
              type = $("input[name='charttype']:checked").val();
              createChart(fetchData,  type)
          });
          $('input[name="datefrom"]').change(function (e) { 
              from = $(this).val();
              loadData();
          });
          $('input[name="dateto"]').change(function (e) { 
              to = $(this).val();
              loadData();
          });
          $('#author').change(function(){
              authorId = $(this).val();
              loadData();
          })
          // Getting All Author
          function fetchAuthor(){
              $.ajax({
                  type: "GET",
                  url: "{{route('getAuthor')}}",
                  success: function (response) {
                      $('#author').html('');
                      let html = `<option value="" >Select Author</option>`;
                      response.forEach(element => {
                          html+=`<option value="${element.id}" id="${element.id}">${element.name}</option>`
                      });
                      $('#author').html(html);
                  }
              });
          }
          

      });
  })(jQuery);
</script>
@endsection