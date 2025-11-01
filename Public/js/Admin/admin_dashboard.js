// document.addEventListener('DOMContentLoaded', function() {
//             const ctx = document.getElementById('pagosChart').getContext('2d');

//             new Chart(ctx, {
//                 type: 'doughnut',
//                 data: {
//                     labels: ['Completado', 'Pendiente', 'Moroso'],
//                     datasets: [{
//                         data: [pagosResumen.pagado, pagosResumen.pendiente, pagosResumen.vencido],
//                         backgroundColor: [
//                             'rgba(79, 70, 229, 0.8)', // Indigo-500
//                             'rgba(251, 191, 36, 0.8)', // Amber-400
//                             'rgba(239, 68, 68, 0.8)'  // Red-500
//                         ],
//                         borderColor: [
//                             'rgba(79, 70, 229, 1)',
//                             'rgba(251, 191, 36, 1)',
//                             'rgba(239, 68, 68, 1)'
//                         ],
//                         borderWidth: 2,
//                         hoverOffset: 20
//                     }]
//                 },
//                 options: {
//                     responsive: true,
//                     maintainAspectRatio: false,
//                     plugins: {
//                         legend: {
//                             position: 'bottom',
//                             labels: {
//                                 font: { size: 14 },
//                                 color: '#374151'
//                             }
//                         },
//                         tooltip: {
//                             callbacks: {
//                                 label: function(context) {
//                                     return context.label + ': ' + context.parsed + ' pagos';
//                                 }
//                             },
//                             backgroundColor: 'rgba(30, 41, 59, 0.9)',
//                             titleColor: '#fff',
//                             bodyColor: '#fff',
//                             padding: 10,
//                             borderRadius: 8
//                         }
//                     },
//                     animation: {
//                         animateRotate: true,
//                         duration: 1500
//                     }
//                 }
//             });
//         });