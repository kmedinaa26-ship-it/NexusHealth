@extends('superadmin.layout')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Evaluacion del Modelo</h1>

    <!-- Tarjetas KPI Modernas -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <dt class="text-sm font-medium text-gray-500 truncate">Accuracy</dt>
                <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ number_format($accuracy * 100, 1) }}%</dd>
            </div>
            <div class="bg-gray-50 px-4 py-4 sm:px-6 h-1 bg-blue-500"></div>
        </div>
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <dt class="text-sm font-medium text-gray-500 truncate">Precision</dt>
                <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ number_format($precision * 100, 1) }}%</dd>
            </div>
            <div class="bg-gray-50 px-4 py-4 sm:px-6 h-1 bg-green-500"></div>
        </div>
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <dt class="text-sm font-medium text-gray-500 truncate">Recall</dt>
                <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ number_format($recall * 100, 1) }}%</dd>
            </div>
            <div class="bg-gray-50 px-4 py-4 sm:px-6 h-1 bg-yellow-500"></div>
        </div>
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <dt class="text-sm font-medium text-gray-500 truncate">F1 Score</dt>
                <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ number_format($f1 * 100, 1) }}%</dd>
            </div>
            <div class="bg-gray-50 px-4 py-4 sm:px-6 h-1 bg-indigo-500"></div>
        </div>
    </div>

    <!-- Matriz de Confusion Moderna -->
    <div class="bg-white shadow rounded-lg overflow-hidden max-w-xl mx-auto">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Matriz de Confusion</h3>
        </div>
        <div class="p-6">
            <table class="w-full text-center">
                <thead>
                    <tr>
                        <th class="p-4"></th>
                        <th class="p-4 text-sm font-medium text-gray-500 uppercase">Real: Vivo</th>
                        <th class="p-4 text-sm font-medium text-gray-500 uppercase">Real: Fallecer</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <tr>
                        <td class="p-4 text-sm font-medium text-gray-900 text-left">Pred: Vivo</td>
                        <td class="p-4">
                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-lg bg-green-50 text-green-700 font-bold text-lg shadow-sm">
                                {{ $tn }}
                            </div>
                            <p class="text-xs text-gray-500 mt-1">TN</p>
                        </td>
                        <td class="p-4">
                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-lg bg-red-50 text-red-700 font-bold text-lg shadow-sm">
                                {{ $fn }}
                            </div>
                            <p class="text-xs text-gray-500 mt-1">FN</p>
                        </td>
                    </tr>
                    <tr>
                        <td class="p-4 text-sm font-medium text-gray-900 text-left">Pred: Fallecer</td>
                        <td class="p-4">
                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-lg bg-yellow-50 text-yellow-700 font-bold text-lg shadow-sm">
                                {{ $fp }}
                            </div>
                            <p class="text-xs text-gray-500 mt-1">FP</p>
                        </td>
                        <td class="p-4">
                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-lg bg-green-50 text-green-700 font-bold text-lg shadow-sm">
                                {{ $tp }}
                            </div>
                            <p class="text-xs text-gray-500 mt-1">TP</p>
                        </td>
                    </tr>
                </tbody>
            </table>
            
            <div class="mt-6 pt-4 border-t border-gray-100 text-xs text-gray-500 flex justify-center gap-6">
                <span><span class="inline-block w-2 h-2 rounded-full bg-green-500 mr-1"></span> Aciertos</span>
                <span><span class="inline-block w-2 h-2 rounded-full bg-yellow-500 mr-1"></span> Falso Positivo</span>
                <span><span class="inline-block w-2 h-2 rounded-full bg-red-500 mr-1"></span> Falso Negativo</span>
            </div>
        </div>
    </div>
</div>
@endsection
