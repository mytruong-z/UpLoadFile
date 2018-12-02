<!DOCTYPE html>
<html>
<head>
    <title><?php echo $title; ?></title>
</head>

<style>
    .container {
        width: 30%;
        margin: auto;
        border-radius: 5px;
        background-color: #f2f2f2;
        padding: 20px;
    }
    input[type=text] {
        width: 100%;
        padding: 12px;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-sizing: border-box;
        margin-top: 6px;
        margin-bottom: 16px;
        resize: vertical;
    }
    input[type=submit] {
        background-color: #4CAF50;
        color: white;
        padding: 12px 20px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }
    input[type=submit]:hover {
        background-color: #45a049;
    }
</style>
<body>
<div class="container">
<h1>Upload multiple files</h1>
<?php echo form_open_multipart();?>
<p>Upload file(s):</p>
<?php echo form_error('uploadedimages[]'); ?>
<?php echo form_upload('uploadedimages[]','','multiple'); ?>
<br />
<br />
<?php echo form_submit('submit','Upload');?>
<?php echo form_close();?>
</div>
</body>
</html>