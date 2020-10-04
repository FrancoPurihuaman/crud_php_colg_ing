<?php include APP_PATH.'/resources/views/partials/head.php' ?>

    <div class="container">
        <div class="row">
            <div class="col-xs-12 text-center">
                <div id="salesChart" class="salesChart"></div>
            </div>
            <div class="col-xs-12 text-center mt-3">
                <button type="button" onClick="ReporVenta()">Actualizar</button>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <p class="change_link text-center">
            <a href="<?php echo PUBLIC_PATH;?>/users/show"> Ver Usuarios </a>
        </p>
    </div>

    <script src="<?php echo PUBLIC_PATH;?>/js/jquery-2.1.4.min.js"></script>
    <script src="<?php echo PUBLIC_PATH;?>/js/bootstrap-3.3.6.min.js"></script>
    <script src="<?php echo PUBLIC_PATH?>/js/librerias/graficos/highcharts.js"></script>
    <script src="<?php echo PUBLIC_PATH?>/js/App.js"></script>
        
    <script type="text/javascript">

        function GraficoVentas($users,$numbers){
            var myChart = Highcharts.chart('salesChart', {
                title: {
                    text: 'GRAFICO - VENTAS'
                },
                subtitle: {
                    text: 'MES DE MARZO'
                },
                xAxis: {
                    title: {
                        text: ''
                    },
                    categories: $users
                },
                yAxis: {
                    title: {
                        text: ''
                    }
                },
                tooltip: {
                    formatter: function() {
                            return '<b>'+ this.series.name +'</b><br/>'+
                            '<b>'+this.x +': '+ this.y ;
                    }
                },
                legend: {
                    layout: 'vertical',
                    align: 'right',
                    verticalAlign: 'middle'
                },
                series: [{
                    name: 'Ventas',
                    data: $numbers
                }]
            });
        }

        function ReporVenta(){
            $.post("<?php echo (PUBLIC_PATH) ?>/users/chart",'',function(data){
                    var users=new Array();
                    var numbers=new Array();
                    $.each(data,function(index,columna){
                        users.push(columna.USU_USER);
                        numbers.push(parseFloat(columna.UG_ID));
                    });
                GraficoVentas(users,numbers)
            },'JSON')
        }
    
        ReporVenta()

    </script>
	</body>
</html>
