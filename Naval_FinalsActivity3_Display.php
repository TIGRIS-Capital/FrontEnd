<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Display</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

</head>
<body>
<div class="container p-5 bg-light">
    <form action="Naval_FinalsActivity3_DB.php" method="post">
        <div class="row g-3">
            <div class="col">
                <input type="search" name="searchinput_jnsa" placeholder="Search" class="form-control">
            </div>
            <div class="col">
                <input type="submit" name="btnsearch_jnsa" value="Search" class="btn btn-primary">
            </div>
        </div>
    </form>

<?php

require_once "Naval_FinalsActivity3_DB.php";

//display query
$displaysql = "Select * from loan_member_jn";

// Converts the string query to an actual query then transfer it to mysql
$result = $conn->query($displaysql);

// Check if the table is empty or not
if ($result->num_rows > 0) {
    echo $result->num_rows . " records found";

?>

    <table class="table table-bordered mt-3">
        <tr>
            <th>Member ID</th>
            <th>Full Name</th>
            <th>Contact Information</th>
            <th>Address</th>
            <th>Image</th>
        </tr>

<?php

    //records
    foreach ($result as $row) {
        echo "<tr>
                <td>" . $row['member_id_jnsa'] . "</td>
                <td>" . $row['member_name_jnsa'] . "</td>
                <td>" . $row['contact_information_jnsa'] . "</td>
                <td>" . $row['address_jnsa'] . "</td>
                <td>    <img src='" . $row['member_img_jnsa'] . "' alt='Member Image' width='100' height='100'>   </td>
              </tr>";
    }
} else {
    echo "No record found";
}

?>
    </table>
</div>
</body>
</html>


