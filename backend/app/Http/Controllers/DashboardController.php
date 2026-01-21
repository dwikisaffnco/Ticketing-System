<?php

namespace App\Http\Controllers;

use App\Http\Resources\DashboardResource;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function getStatistics()
    {
        $currentMonth = Carbon::now()->startOfMonth();
        $endOfMonth = $currentMonth->copy()->endOfMonth();

        $totalTickets = Ticket::whereBetween('created_at', [$currentMonth, $endOfMonth])->count();

        $activeTickets = Ticket::whereBetween('created_at', [$currentMonth, $endOfMonth])
            ->where('status', '!=', 'resolved')
            ->count();

        $resolvedTickets = Ticket::whereBetween('created_at', [$currentMonth, $endOfMonth])
            ->where('status', 'resolved')
            ->count();

        $archivedTickets = Ticket::whereBetween('created_at', [$currentMonth, $endOfMonth])
            ->whereNotNull('archived_at')
            ->count();

        $avgResolutionTime = Ticket::whereBetween('created_at', [$currentMonth, $endOfMonth])
            ->where('status', 'resolved')
            ->whereNotNull('completed_at')
            ->select(DB::raw('AVG(TIMESTAMPDIFF(HOUR, created_at, completed_at)) as avg_time'))
            ->value('avg_time') ?? 0;

        $statusDistribution = [
            'open' => Ticket::whereBetween('created_at', [$currentMonth, $endOfMonth])->where('status', 'open')->count(),
            'onprogress' => Ticket::whereBetween('created_at', [$currentMonth, $endOfMonth])->where('status', 'onprogress')->count(),
            'resolved' => Ticket::whereBetween('created_at', [$currentMonth, $endOfMonth])->where('status', 'resolved')->count(),
            'rejected' => Ticket::whereBetween('created_at', [$currentMonth, $endOfMonth])->where('status', 'rejected')->count(),
        ];

        $priorityDistribution = [
            'low' => Ticket::whereBetween('created_at', [$currentMonth, $endOfMonth])->where('priority', 'low')->count(),
            'medium' => Ticket::whereBetween('created_at', [$currentMonth, $endOfMonth])->where('priority', 'medium')->count(),
            'high' => Ticket::whereBetween('created_at', [$currentMonth, $endOfMonth])->where('priority', 'high')->count(),
        ];

        $ticketTrends = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $count = Ticket::whereDate('created_at', $date)->count();
            $ticketTrends[] = [
                'date' => $date,
                'count' => $count,
            ];
        }

        $dashboardData = [
            'total_tickets' => $totalTickets,
            'active_tickets' => $activeTickets,
            'resolved_tickets' => $resolvedTickets,
            'archived_tickets' => $archivedTickets,
            'avg_resolution_time' => round($avgResolutionTime, 1),
            'status_distribution' => $statusDistribution,
            'priority_distribution' => $priorityDistribution,
            'ticket_trends' => $ticketTrends,
        ];

        return response()->json([
            'message' => 'Dashboard statistics fetched successfully',
            'data' => new DashboardResource($dashboardData)
        ]);
    }
}
