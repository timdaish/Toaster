<html>
<head>
  <title>Hello AJAX!</title>
</head>
<body>
<button>Use Ajax to get and then run a JavaScript</button>
<script src="http://code.jquery.com/jquery-2.1.4.min.js"></script>
<script>

$.ajaxSetup({
  cache: true
});

$("button").click(function(){
    $.getScript( "http://localhost/toast/testjs.js" )
      .done(function( script, textStatus,jqxhr ) {
      //console.log( script ); // Data returned
      //  console.log( textStatus );
      //  console.log( jqxhr.status ); // 200
      console.log( "JS was load and executed." );
      })
      .fail(function( jqxhr, settings, exception ) {
        $( "div.log" ).text( "Triggered ajaxError handler." );
    });
});


</script>


</body>
</html>