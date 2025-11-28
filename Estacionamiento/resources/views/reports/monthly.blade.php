<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte Mensual - {{ $month }}/{{ $year }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Reporte Mensual - {{ $month }}/{{ $year }}</h1>

    <h2>Ventas del Mes</h2>
    <p>Total Ventas: ${{ $sales->total_sales ?? 0 }}</p>
    <p>Total Transacciones: {{ $sales->total_transactions ?? 0 }}</p>

    <h2>Resumen Mensual</h2>
    <p>Tasa de Ocupación: {{ $monthlyReview->average_occupancy ?? 0 }}%</p>
    <p>Horas Pico: {{ $monthlyReview->peak_hours ?? 'N/A' }}</p>
    <p>Reservaciones: {{ $monthlyReview->reservation_count ?? 0 }}</p>
    <p>Cancelaciones: {{ $monthlyReview->cancelled_reservations ?? 0 }}</p>
    <p>Tasa de Confirmación: {{ $monthlyReview->reservation_rate ?? 0 }}%</p>

    <h2>Estadísticas de Ocupación</h2>
    <table>
        <thead>
            <tr>
                <th>Espacio</th>
                <th>Minutos Ocupados</th>
                <th>Tasa de Utilización (%)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($occupancyStats->occupancy_logs ?? [] as $stat)
            <tr>
                <td>{{ $stat->espacio }}</td>
                <td>{{ $stat->occupied_minutes }}</td>
                <td>{{ $stat->utilization_rate }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
