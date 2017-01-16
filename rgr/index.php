<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<style>
table { border-collapse:separate; border:none; border-spacing:0; margin:8px 12px 18px 6px; line-height:1.2em; margin-left:auto; margin-right:auto; overflow: auto }
table th { font-weight:bold; background:#666; color:white; border:1px solid #666; border-right:1px solid white }
table th:last-child { border-right:1px solid #666 }
table caption { font-style:italic; margin:10px 0 20px 0; text-align:center; color:#666; font-size:1.2em }
tr{ border:none }
td { border:1px solid #666; border-width:1px 1px 0 0 }
td, th { padding:15px }
tr td:first-child { border-left-width:1px }
tr:last-child td { border-bottom-width:1px }
</style>
</head>

<body>

<?

require_once 'work_with_db.php';

$object = new Work_with_db('localhost', 'root',  '');
$link = $object->ConnectToDb('testdb');
$object->SetDbEncoding('SET NAMES utf8');
$object->SendSqlRequest('select * from MyGuests');
$object->DeleteRecord('MyGuests', array(
    'id' => 'DENIS',
    'lastname' => 'IVANOV',
    'email' => 'Mail'
), 4);
$last_inserted_id = $object->AddNewRecord("MyGuests", array(
    "firstname" => "bar",
    "lastname" => "foo",
    "email" => "foo"
));
echo ' last inserted id: ', $last_inserted_id, ' ';
$object->UpdateRecord('MyGuests', array(
    'firstname' => 'Denis',
    'lastname' => 'ivanov',
), array(
    'firstname' => 'DENIS',
    'lastname' => 'IVANOV',
    'email' => 'Mail'
), 4);
$object->PrintTables();
$someRecords = $object->GetRecords('MyGuests', array(
    'firstname' => 'pavel',
    'lastname' => 'IVANOV',
), 7,'id ASC');

while ($row = $someRecords->fetch_assoc()) {
    echo "<pre>", print_r($row), "</pre>";
}

$amount_of_lines = $object->GetAmountOfLines('MyGuests');
echo 'amount of lines: ', $amount_of_lines, ' ';
$object->Disconnect(); 
?>


</body>
</html>
