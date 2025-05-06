<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Detalle XML</title>
    <style>
        body {
          font-family: Arial, sans-serif;
        }
        .table {
          width: 100%;
          border-collapse: collapse;
          margin-bottom: 20px;
        }
        .table th, .table td {
          border: 1px solid #ddd;
          padding: 8px;
        }
        .table th {
          background-color: #f2f2f2;
          text-align: left;
        }
        b {
          font-size: 1.2em;
          display: block;
          margin-top: 20px;
        }
        .scroll-container {
          max-height: 250px;
          overflow: auto;
          border: 1px solid #ccc;
          padding: 10px;
        }
    </style>
</head>
<body>

<?= $xmlContent ?>

</body>
</html>
