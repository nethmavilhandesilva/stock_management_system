<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $reportTitle ?? 'Report' }}</title>

    <style>
        /*
         * The @font-face rules are removed here because they are handled
         * directly by Dompdf's font registration in the controller.
         * This avoids issues with relative paths during PDF generation.
         */

        body, table, th, td, h2, h4, p {
            font-family: 'NotoSansSinhala', sans-serif;
            font-size: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th, td {
            border: 1px solid black;
            padding: 5px;
            text-align: left;
        }

        .text-end {
            text-align: right;
        }

        h2, h4 {
            text-align: center;
            margin: 0;
        }
    </style>
</head>
<body>
    <h2>TGK ට්‍රේඩර්ස්</h2>
    <h4>{{ $reportTitle ?? 'Report' }}</h4>
    <p>Report Date: {{ \Carbon\Carbon::now()->format('Y-m-d H:i') }}</p>

    <table>
        <thead>
            <tr>
                @foreach($headings as $heading)
                    <th>{{ $heading }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($reportData as $row)
                <tr>
                    @foreach($row as $cell)
                        <td>{{ $cell }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>