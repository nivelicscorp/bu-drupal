google.charts.load('current', {packages: ['corechart', 'bar']});
(function ($, Drupal) {
  Drupal.behaviors.searcherModal = {
    ColumnaDrawBasic: function (id_canvas, data_graphs) {
      
      var graphs_bar = new Array();
      $.each(data_graphs.item_data, function( index, value ) {
        var items_graphs_bar = new Array();
        $.each(value, function( indexsub, valuesub ) {
          var seve_value = "";
          if (index == 0) {
            seve_value = valuesub;
          } else {
            seve_value =  (indexsub == 0)?valuesub:parseFloat(valuesub);
          }
          items_graphs_bar.push(seve_value)
        });
        graphs_bar.push(items_graphs_bar);
      });
      var data = google.visualization.arrayToDataTable(graphs_bar, false);
      var options = {
        title : data_graphs.title,
        vAxis: {title: data_graphs.title_x},
        hAxis: {title: data_graphs.title_y},
        seriesType: 'bars',
        series: {5: {type: 'line'}}
      };
      var chart = new google.visualization.ComboChart(document.getElementById('chart_div-' + id_canvas));
      chart.draw(data, options);
    }, 
    BarDrawBasic: function (id_canvas, data_graphs) {
      var graphs_bar = new Array();
      $.each(data_graphs.item_data, function( index, value ) {
        var items_graphs_bar = new Array();
        $.each(value, function( indexsub, valuesub ) {
          var seve_value = "";
          if (index == 0) {
            seve_value = valuesub;
          } else {
            seve_value =  (indexsub == 0)?valuesub:parseFloat(valuesub);
          }
          items_graphs_bar.push(seve_value)
        });
        graphs_bar.push(items_graphs_bar);
      });
      var data = google.visualization.arrayToDataTable(graphs_bar, false);
      var options = {
        title: data_graphs.title,
        chartArea: {width: '50%'},
        annotations: {
          alwaysOutside: true,
          textStyle: {
            fontSize: 12,
            auraColor: 'none',
            color: '#555'
          },
          boxStyle: {
            stroke: '#ccc',
            strokeWidth: 1,
            gradient: {
              color1: '#f3e5f5',
              color2: '#f3e5f5',
              x1: '0%', y1: '0%',
              x2: '100%', y2: '100%'
            }
          }
        },
        hAxis: {
          title: data_graphs.title_y,
          minValue: 0,
        },
        vAxis: {
          title: data_graphs.title_x
        }
      };
      var chart = new google.visualization.BarChart(document.getElementById('chart_div-' + id_canvas));
      chart.draw(data, options);
    }, 
    PieDrawBasic: function (id_canvas, data_graphs) {
      var graphs_bar = new Array();
      $.each(data_graphs.item_data, function( index, value ) {
        var items_graphs_bar = new Array();
        $.each(value, function( indexsub, valuesub ) {
          if (indexsub < 2) {
            var seve_value = "";
            if (index == 0) {
              seve_value = valuesub;
            } else {
              seve_value =  (indexsub == 0)?valuesub:parseFloat(valuesub);
            }
            items_graphs_bar.push(seve_value);
          }  
        });
        graphs_bar.push(items_graphs_bar);
      });
      var data = google.visualization.arrayToDataTable(graphs_bar, false);
      var options = {
        title: data_graphs.title
      };

      var chart = new google.visualization.PieChart(document.getElementById('chart_div-' + id_canvas));
      chart.draw(data, options);
    },
    LineDrawBasic: function (id_canvas, data_graphs) {
      var data = new google.visualization.DataTable();
      

      var graphs_bar = new Array();
      $.each(data_graphs.item_data, function( index, value ) {
        var items_graphs_bar = new Array();
        $.each(value, function( indexsub, valuesub ) {
          var seve_value = "";
          if (index == 0) {
            data.addColumn('number', valuesub);
            seve_value = valuesub;
          } else {
            seve_value =  (indexsub == 0)?valuesub:items_graphs_bar.push(parseFloat(valuesub));
          }
        });
        if (items_graphs_bar.length > 0) {
          graphs_bar.push(items_graphs_bar);
        }
        
      });

      data.addRows(graphs_bar);
      var options = {
        title: data_graphs.title,
        hAxis: {
          title: data_graphs.title_y,
          logScale: true
        },
        vAxis: {
          title: data_graphs.title_x,
          logScale: false
        },
      };
      var chart = new google.visualization.LineChart(document.getElementById('chart_div-' + id_canvas));
      chart.draw(data, options);
    },
    AreaDrawBasic: function (id_canvas, data_graphs) {
      var graphs_bar = new Array();
      $.each(data_graphs.item_data, function( index, value ) {
        var items_graphs_bar = new Array();
        $.each(value, function( indexsub, valuesub ) {
          var seve_value = "";
          if (index == 0) {
            seve_value = valuesub;
          } else {
            seve_value =  (indexsub == 0)?valuesub:parseFloat(valuesub);
          }
          items_graphs_bar.push(seve_value)
        });
        graphs_bar.push(items_graphs_bar);
      });
      var data = google.visualization.arrayToDataTable(graphs_bar, false);
      var options = {
        title: data_graphs.title,
        hAxis: {title: data_graphs.title_y,  titleTextStyle: {color: '#333'}},
        vAxis: {title: data_graphs.title_x, minValue: 0}
      };
      var chart = new google.visualization.AreaChart(document.getElementById('chart_div-' + id_canvas));
      chart.draw(data, options);
    }, 
    PiechartDrawBasic: function (id_canvas, data_graphs) {
      var graphs_bar = new Array();
      $.each(data_graphs.item_data, function( index, value ) {
        var items_graphs_bar = new Array();
        $.each(value, function( indexsub, valuesub ) {
          var seve_value = "";
          if (index == 0) {
            seve_value = valuesub;
          } else {
            seve_value =  (indexsub == 0)?valuesub:parseFloat(valuesub);
          }
          items_graphs_bar.push(seve_value)
        });
        graphs_bar.push(items_graphs_bar);
      });
      
      var data = google.visualization.arrayToDataTable(graphs_bar, false);

      var options = {
        title: data_graphs.title,
        pieHole: 0.4,
      };

      var chart = new google.visualization.PieChart(document.getElementById('chart_div-' + id_canvas));
      chart.draw(data, options);
    },
    attach: function (context, settings) {
      $.each(drupalSettings.searcher_modal.graphs, function( index, value ) {
        var $graphs_d = JSON.parse(value);
        console.log($graphs_d.type);
        switch ($graphs_d.type) {
          case 'columna':
            google.charts.setOnLoadCallback(Drupal.behaviors.searcherModal.ColumnaDrawBasic(index,$graphs_d));
          break;
          case 'bar':
            google.charts.setOnLoadCallback(Drupal.behaviors.searcherModal.BarDrawBasic(index,$graphs_d));
            break;
          case 'pie':
            google.charts.setOnLoadCallback(Drupal.behaviors.searcherModal.PieDrawBasic(index,$graphs_d));
            break; 
          case 'line':
            google.charts.setOnLoadCallback(Drupal.behaviors.searcherModal.LineDrawBasic(index,$graphs_d));
            break;
          case 'area':
            google.charts.setOnLoadCallback(Drupal.behaviors.searcherModal.AreaDrawBasic(index,$graphs_d));
            break;
          case 'piechart':
            google.charts.setOnLoadCallback(Drupal.behaviors.searcherModal.PiechartDrawBasic(index,$graphs_d));
            break;      

        }
       

      });



      
    }
  };
})(jQuery, Drupal);