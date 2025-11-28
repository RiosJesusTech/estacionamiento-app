<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte Diario - {{ $date }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Reporte Diario - {{ $date }}</h1>

    <h2>Ventas del Día</h2>
    <p>Total Ventas: ${{ $sales->total_sales ?? 0 }}</p>
    <p>Total Transacciones: {{ $sales->total_transactions ?? 0 }}</p>

    <h2>Estadísticas de Ocupación</h2>
    <p>Ocupación Promedio: {{ $occupancyStats->average_occupancy ?? 0 }}%</p>
    <p>Horas Pico de Entradas: {{ $occupancyStats->peak_entry_hours ?? 'N/A' }}</p>
    <p>Entradas en Horas Pico: {{ $occupancyStats->peak_entry_count ?? 0 }}</p>

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
