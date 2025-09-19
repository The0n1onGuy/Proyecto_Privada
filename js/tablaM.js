
$(document).ready(function() {
    $('#myTable').DataTable({
        "ajax": {
            "url": "../clases/obtenClient.php", 
            "dataSrc": "data"           
        },
        
        "columns": [
            { "data": "customerName" },
            { "data": "phone" },
            { "data": "city" }
        ],
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
        }
    });
});

// var MycustomD = [
//     {
//         "name":       "Tiger Nixon",
//         "position":   "System Architect",
//         "salary":     "$3,120",
//         "start_date": "2011/04/25",
//         "office":     "Edinburgh",
//         "extn":       "5421"
//     },
//     {
//         "name":       "Garrett Winters",
//         "position":   "Director",
//         "salary":     "$5,300",
//         "start_date": "2011/07/25",
//         "office":     "Edinburgh",
//         "extn":       "8422"
//     }
// ]

// $('#myTable').DataTable( {
//     data: MycustomD,
//     columns: [
//         { data: 'name' },
//         { data: 'position' },
//         { data: 'salary' },
//         { data: 'office' }
//     ]
// } );