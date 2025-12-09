// Only the male/female chart - no second chart
document.addEventListener('DOMContentLoaded', function() {
    var ctx = document.getElementById('myChart').getContext('2d');
    
    var myChart = new Chart(ctx, {
        type: 'polarArea',
        data: {
            labels: ['Male Teachers', 'Female Teachers'],
            datasets: [{
                label: 'Teachers by Gender',
                data: [teacherData.male, teacherData.female],
                backgroundColor: [
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 99, 132, 1)'
                ],
            }]
        },
        options: { 
            responsive: true,
            maintainAspectRatio: true
        }
    });
});