<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $reportTitle ?? 'Report' }}</title>
    <style>
        /* Use your Sinhala font for everything */
        body, table, th, td, h2, h4, p {
            font-family: 'notosanssinhala', sans-serif;
            font-size: 10px;
            line-height: 1.3;
        }

        /* Center titles */
        h2, h4 {
            text-align: center;
            margin: 5px 0;
        }

        /* Report info */
        p.report-date {
            text-align: right;
            margin: 5px 0 10px 0;
        }

        /* Table styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
            vertical-align: middle;
        }

        th {
            background-color: #f2f2f2;
        }

        .text-end {
            text-align: right;
        }

        /* Optional: Zebra stripes for better readability */
        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>

    <h2>TGK ට්‍රේඩර්ස්</h2>
    <h4>{{ $reportTitle ?? 'Report' }}</h4>

    <p class="report-date">Report Date: {{ \Carbon\Carbon::now()->format('Y-m-d H:i') }}</p>

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
