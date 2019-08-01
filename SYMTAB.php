
<!DOCTYPE html>
<html>
<head>
    <title>Assembler</title>
      <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

  <link href="//netdna.bootstrapcdn.com/bootstrap/3.1.0/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
<script src="//netdna.bootstrapcdn.com/bootstrap/3.1.0/js/bootstrap.min.js"></script>
<script src="//code.jquery.com/jquery-1.11.1.min.js"></script>
</head>
<body>
<?php
error_reporting(E_ERROR | E_PARSE);

$con = mysqli_connect("localhost","root","","assembler");
if (mysqli_connect_errno())
{
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

$code = isset($_REQUEST['text'])?$_REQUEST['text']:"";

$size =0;$section=0;$offset=0; //global variables

if($code){
  $line = preg_split( "/(\s+|,|\.|\?)/", $code );

  //print_r($line);

    echo '<div class="container"><div class="row"><div class="container"><div class="panel panel-primary filterable">
            <div class="panel-heading">
                <h3 class="panel-title">Symbol Table</h3>
                <div class="pull-right">
                    <button class="btn btn-default btn-xs btn-filter"><span class="glyphicon glyphicon-filter"></span> Filter</button>
                </div>
            </div>';
echo "<table border='1' class='table'>
                <thead>
                    <tr class='filters'>
                        
                        <th><input type='text' class='form-control' placeholder='Name' disabled></th>
                        <th><input type='text' class='form-control' placeholder='Type' disabled></th>
                        <th><input type='text' class='form-control' placeholder='Size' disabled></th>
                        <th><input type='text' class='form-control' placeholder='Offset' disabled></th>
                        <th><input type='text' class='form-control' placeholder='Section_id' disabled></th>
                        <th><input type='text' class='form-control' placeholder='Is_global' disabled></th>
                        <th><input type='text' class='form-control' placeholder='Is_initializable' disabled></th>
                        
                    </tr>
                </thead>";
   $flag=false;              // flag to keep track of string
  foreach ($line as $index=>$var) {
    $MOT = mysqli_query($con,"Select * from MOT where Mnemonic='$var'");
    $POT = mysqli_query($con,"Select * from POT where Pseudo_opcode='$var'");
    $Register = mysqli_query($con,"Select * from Register where Name='$var'");
    $Interrupt = mysqli_query($con,"Select * from Interrupt where Number='$var'");
    $Directives = mysqli_query($con,"Select * from Directives where Name='$var'");

              
     if($var{0}=="\""){
            unset($var);
            $flag=true;
          }
      if($var{strlen($var)-1}=="\"") {
        $flag=false;
        unset($var);
        continue;

      }   
     if($flag==true){
      unset($var);
      continue;
     } 

     if(preg_match("/[A-Za-z]+:[A-Za-z]+/", $var) || preg_match("/[A-Za-z]+\[[A-Za-z]+/", $var) || preg_match('/[\'\/~`\!@#\$%\^&\*\(\)_\-\+=\{\}\[\]\|;"\<\>,\.\?\\\]/', $var)){

      unset($var);
      continue;
     }   

     if(preg_match("/segment/i", $var) || preg_match("/\.code|\.data/i", $var)){
      $section++;
      $offset=0;
      
     }




    if(mysqli_num_rows($MOT) == 0 && mysqli_num_rows($POT) == 0 && mysqli_num_rows($Register) == 0 && mysqli_num_rows($Interrupt) == 0 && mysqli_num_rows($Directives) == 0  && !is_numeric($var) && !is_float($var) && !is_bool($var) && $var{strlen($var)-1}!="h" && $var{strlen($var)-1}!="H" && $var != ""){
          // if word is not in MOT or POT or REGISTER or INTERRUPT or DIRECTIVES or INTEGER or STRING

          if ($var{strlen($var)-1} ==":") {

              $varUpdated = substr($var, 0, strlen($var)-1);
              $SYMTAB = mysqli_query($con,"Insert into SYMTAB values('$varUpdated','Undefined',0,$offset,'$section',0,0)");

          }
          else {

              $SYMTAB = mysqli_query($con,"Insert into SYMTAB values('$var','Undefined',0,$offset,'$section',0,0)");      
          }

         $next =$line[$index+1];
         //echo $line[$index],$var{strlen($var)-1},"<br>";
        // $query = mysqli_query($con,"Select * from POT where Pseudo_opcode='$temp'");
                
                //if(mysqli_num_rows($query) == 0){
                if ($labelCheck = mysqli_query($con, "Select Type from SYMTAB where Name='$var'")) {
                    $typeAr = mysqli_fetch_array($labelCheck);

                    $type = $typeAr['Type'];
                  if ($type == 'Undefined') { //Dont set to Variable if its a Label
                      mysqli_query($con,"Update SYMTAB set Type='Variable',Is_initializable=1 where Name='$var'");
                  }
                }
                  
                if($next ==":" || $var{strlen($var)-1} ==":"){
                  //$var = substr($var,0,strlen($var)-1);
                  //$varUpdated = substr($var, 0, strlen($var)-1);
                  mysqli_query($con,"Update SYMTAB set Type='Label',Is_initializable=0 where Name='$varUpdated'");

                  
                }
                else if(preg_match("/EXTRN/i", $line[$index-1])){
                  
                  mysqli_query($con,"Update SYMTAB set Type='External',Is_initializable=0 where Name='$var'");
                  $offset += 1;
                }

                else if(preg_match("/GLOBAL/i", $line[$index-1])){
                  
                  mysqli_query($con,"Update SYMTAB set Is_global=1,Is_initializable=0 where Name='$var'");
                  $offset += 1;
                }


              //}
              //next($line);

    }

      
        else{
            // if word is in MOT or POT or REGISTER
            if(mysqli_num_rows($POT) != 0){
              //echo "hello";
              $row = mysqli_fetch_array($POT);
              $size = $row['Size'];
              $prev = $line[$index -1];
              mysqli_query($con,"Update SYMTAB set Size='$size' where Name='$prev'");
              $next = $line[$index+1];
             // echo $next.gettype($next);
              $count=0;
              $i = $index+1;
              if(is_numeric($next)){  //for array size
                //echo "inside if";
                while(is_numeric($next)){
                 // echo "inside while";
                  $count++;
                  $next = $line[++$i];
                }
                $size *=$count;

                //echo $size;
                mysqli_query($con,"Update SYMTAB set Size='$size' where Name='$prev'");
              }

              $offset += $size;

          }

          if (mysqli_num_rows($MOT) != 0) {

                  $row = mysqli_fetch_array($MOT);
                  $size = $row['Size'];

                  $offset += $size;

                  //echo $offset."<br>";

          }
          
          }

         

  }

}

else{
  // if no code was written on the textarea
echo "Nothing written";
}

//$query = mysqli_query($con,"DELETE FROM SYMTAB WHERE LOWER(Name) LIKE SUBSTRING(Select Name from SYMTAB,0,LEN(Name))") or die("no");
$query = mysqli_query($con,"Select * from SYMTAB as a , SYMTAB as b where b.Name = SUBSTR(a.Name,0,LENGTH(a.Name)-1)");
while($row = mysqli_fetch_array($query))
{
echo $row['a.Name'];
}  

echo '<tbody>';               


$result = mysqli_query($con,"SELECT * FROM SYMTAB order by Section_id");
while($row = mysqli_fetch_array($result))
{
  
echo "<tr>";
echo "<td>" . $row['Name'] . "</td>";
echo "<td>" . $row['Type'] . "</td>";
if($row['Size'] ==1)
echo "<td>" . $row['Size'] ." Byte". "</td>";
elseif($row['Size']>1)
echo "<td>" . $row['Size'] ." Bytes". "</td>";
else
echo "<td>" . "-". "</td>";
echo "<td>" . $row['Offset'] . "</td>";
echo "<td>" . $row['Section_id'] . "</td>";
if($row['Is_global']==0)
echo "<td>" . "FALSE". "</td>";
else
echo "<td>" . "TRUE". "</td>";
if($row['Is_initializable']==0)
echo "<td>" ."FALSE". "</td>";
else
echo "<td>" . "TRUE". "</td>";
echo "</tr>";

}
echo "</tbody>";
echo "</table></div></div></div>";
$result = mysqli_query($con,"Delete from SYMTAB");
mysqli_close($con);
?>

</body>
<script type="text/javascript">
    /*
Please consider that the JS part isn't production ready at all, I just code it to show the concept of merging filters and titles together !
*/
$(document).ready(function(){
    $('.filterable .btn-filter').click(function(){
        var $panel = $(this).parents('.filterable'),
        $filters = $panel.find('.filters input'),
        $tbody = $panel.find('.table tbody');
        if ($filters.prop('disabled') == true) {
            $filters.prop('disabled', false);
            $filters.first().focus();
        } else {
            $filters.val('').prop('disabled', true);
            $tbody.find('.no-result').remove();
            $tbody.find('tr').show();
        }
    });

    $('.filterable .filters input').keyup(function(e){
        /* Ignore tab key */
        var code = e.keyCode || e.which;
        if (code == '9') return;
        /* Useful DOM data and selectors */
        var $input = $(this),
        inputContent = $input.val().toLowerCase(),
        $panel = $input.parents('.filterable'),
        column = $panel.find('.filters th').index($input.parents('th')),
        $table = $panel.find('.table'),
        $rows = $table.find('tbody tr');
        /* Dirtiest filter function ever ;) */
        var $filteredRows = $rows.filter(function(){
            var value = $(this).find('td').eq(column).text().toLowerCase();
            return value.indexOf(inputContent) === -1;
        });
        /* Clean previous no-result if exist */
        $table.find('tbody .no-result').remove();
        /* Show all rows, hide filtered ones (never do that outside of a demo ! xD) */
        $rows.show();
        $filteredRows.hide();
        /* Prepend no-result row if all rows are filtered */
        if ($filteredRows.length === $rows.length) {
            $table.find('tbody').prepend($('<tr class="no-result text-center"><td colspan="'+ $table.find('.filters th').length +'">No result found</td></tr>'));
        }
    });
});
</script>
</html>