 <script type="text/javascript">
     var table = new Tabulator("#tabelle1", {
         ajaxURL:"http://ralfwork.localhost:90/betrieb/work/monitor/data/data.json", //ajax URL
         //data:tabledata, //assign data to table
         persistentFilter:true, //Enable filter persistence
         //height:205, // set height of table (in CSS or here), this enables the Virtual DOM and improves render speed dramatically (can be any valid css height value)
         layout:"fitData", //fit columns to width of table (optional)
         columns:[ //Define Table Columns
             {title:"Date", field:"date", sorter:"date", align:"center", headerFilter: true},
             {title:"Status WEM", field:"status_web", align:"right", width:150, headerFilter: true},
             {title:"Status Var", field:"status_varnish", align:"right", width:150, headerFilter: true},
             {title:"avg Var", field:"avg_w", align:"right", formatter:"progress",formatterParams:{min:0, max:30,}},
             {title:"avg Var+WEM", field:"avg_vw", align:"right", formatter:"progress",formatterParams:{min:0, max:30,}},
             {title:"avg Var", field:"avg_v", align:"right", formatter:"progress",formatterParams:{min:0, max:30,}},
             {title:"sec1 WEM", field:"wert_1w", align:"right", formatter:"progress",formatterParams:{min:0, max:30,}},
             {title:"sec1 Var+WEM", field:"wert_1vw", align:"right", formatter:"progress",formatterParams:{min:0, max:30,}},
             {title:"sec1 Var", field:"wert_1v", align:"right", formatter:"progress",formatterParams:{min:0, max:30,}},
             {title:"sec2 WEM", field:"wert_2w", align:"right", formatter:"progress",formatterParams:{min:0, max:30,}},
             {title:"sec2 Var+WEM", field:"wert_2vw", align:"right", formatter:"progress",formatterParams:{min:0, max:30,}},
             {title:"sec2 Var", field:"wert_2v", align:"right", formatter:"progress",formatterParams:{min:0, max:30,}},
             {title:"sec3 WEM", field:"wert_3w", align:"right", formatter:"progress",formatterParams:{min:0, max:30,}},
             {title:"sec3 Var+WEM", field:"wert_3vw", align:"right", formatter:"progress",formatterParams:{min:0, max:30,}},
             {title:"sec3 Var", field:"wert_3v", align:"right", formatter:"progress",formatterParams:{min:0, max:30,}},
             {title:"url1 WEM", field:"url_1w", align:"left"},
             {title:"url1 Var+WEM", field:"url_1vw", align:"left"},
             {title:"url2 WEM", field:"url_2w", align:"left"},
             {title:"url2 Var+WEM", field:"url_2vw", align:"left"},
             {title:"url3 WEM", field:"url_3w", align:"left"},
             {title:"url3 Var+WEM", field:"url_3vw", align:"left"},
         ],
         rowClick:function(e, row){ //trigger an alert message when the row is clicked
             alert("Row " + row.getData().id + " Clicked!!!!");
         },
     });

			jQuery(document).ready(function(){

			});

 </script>