




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
	
<button type="button" class="btn btn-success" id="mot" onclick="MOT()">Machine Opcode Table</button>
<button type="button" class="btn btn-success" id="pot" onclick="POT()">Psuedo Opcode Table</button>
<button type="button" class="btn btn-success" id="sym">Symbol Table</button>

<?php
$con=mysqli_connect("localhost","root","","assembler");
// Check connection
if (mysqli_connect_errno())
{
echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

function MOT(){
$result = mysqli_query($con,"SELECT * FROM MOT");

        echo '<div class="container"><div class="row"><div class="container"><div class="panel panel-primary filterable">
            <div class="panel-heading">
                <h3 class="panel-title">MOT</h3>
                <div class="pull-right">
                    <button class="btn btn-default btn-xs btn-filter"><span class="glyphicon glyphicon-filter"></span> Filter</button>
                </div>
            </div>';
echo "<table border='1' class='table'>
                <thead>
                    <tr class='filters'>
                        
                        <th><input type='text' class='form-control' placeholder='Mnemonic' disabled></th>
                        <th><input type='text' class='form-control' placeholder='Size' disabled></th>
                        <th><input type='text' class='form-control' placeholder='Opcode' disabled></th>
                        
                    </tr>
                </thead>
<tbody>";               



while($row = mysqli_fetch_array($result))
{
echo "<tr>";
echo "<td>" . $row['Mnemonic'] . "</td>";
echo "<td>" . $row['Size'] . "</td>";
echo "<td>" . $row['Opcode'] . "</td>";
echo "</tr>";
}
echo "</tbody>";
echo "</table></div></div></div>";


}

function POT(){
$result = mysqli_query($con,"SELECT * FROM POT");

        echo '<div class="container"><div class="row"><div class="container"><div class="panel panel-primary filterable">
            <div class="panel-heading">
                <h3 class="panel-title">MOT</h3>
                <div class="pull-right">
                    <button class="btn btn-default btn-xs btn-filter"><span class="glyphicon glyphicon-filter"></span> Filter</button>
                </div>
            </div>';
echo "<table border='1' class='table'>
                <thead>
                    <tr class='filters'>
                        
                        <th><input type='text' class='form-control' placeholder='Pseudo-opcode' disabled></th>
                        <th><input type='text' class='form-control' placeholder='Type' disabled></th>
                        <th><input type='text' class='form-control' placeholder='Size' disabled></th>
                        <th><input type='text' class='form-control' placeholder='Initializable' disabled></th>
                        
                    </tr>
                </thead>
<tbody>";               



while($row = mysqli_fetch_array($result))
{
echo "<tr>";
echo "<td>" . $row['Pseudo-opcode'] . "</td>";
echo "<td>" . $row['Type'] . "</td>";
echo "<td>" . $row['Size'] . "</td>";
echo "<td>" . $row['Initializable'] . "</td>";
echo "</tr>";
}
echo "</tbody>";
echo "</table></div></div></div>";


}
mysqli_close($con);
?>

</body>
<style type="text/css">
	button{
		margin-left: 200px;
		margin-top: 50px;
	}

	.filterable {
    margin-top: 15px;
}
.filterable .panel-heading .pull-right {
    margin-top: -20px;
}
.filterable .filters input[disabled] {
    background-color: transparent;
    border: none;
    cursor: auto;
    box-shadow: none;
    padding: 0;
    height: auto;
}
.filterable .filters input[disabled]::-webkit-input-placeholder {
    color: #333;
}
.filterable .filters input[disabled]::-moz-placeholder {
    color: #333;
}
.filterable .filters input[disabled]:-ms-input-placeholder {
    color: #333;
}

</style>
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