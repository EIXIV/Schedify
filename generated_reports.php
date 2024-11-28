<!DOCTYPE html>
<html>
<head>
    <title>Generated Reports</title>
    <!-- Include DataTables CSS and jQuery -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.js"></script>
    
    <style>
        /* Custom modal styling */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5); /* Black background with transparency */
            overflow-y: auto; /* Allow scrolling in the modal */
        }

        .modal-content {
            background-color: white;
            margin: 5% auto; /* Adjust the vertical spacing */
            padding: 20px;
            border: 1px solid #888;
            width: 50%;
            max-height: 80vh; /* Max height of modal: 80% of the viewport */
            overflow-y: auto; /* Enable scrolling inside the modal if content overflows */
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover, .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table, th, td {
            border: 1px solid black;
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .btn {
            padding: 5px 10px;
            cursor: pointer;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 3px;
        }

        .btn:hover {
            background-color: #45a049;
        }

        /* Style for the course table inside the modal */
        #courses-table {
            margin-top: 20px;
        }

    </style>
</head>
<body>
    <h1>Generated Reports</h1>
    <table id="reports-table" class="display">
        <thead>
            <tr>
                <th>Program</th>
                <th>Section</th>
                <th>Academic Year</th>
                <th>Semester</th>
                <th>Year</th>
                <th>Reference Number</th>
                <th>Total Units</th>
                <th>Created At</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <!-- Reports will be appended here via JavaScript -->
        </tbody>
    </table>

    <!-- Custom Modal -->
    <div id="reportModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Report Details</h2>
            <div id="reportContent">
                <!-- Report details will be injected here -->
            </div>
            <h3>Courses in this Report</h3>
            <table id="courses-table">
                <thead>
                    <tr>
                        <th>Course Code</th>
                        <th>Course Name</th>
                        <th>Units</th>
                    </tr>
                </thead>
                <tbody id="courses-list">
                    <!-- Courses will be injected here -->
                </tbody>
            </table>
        </div>
    </div>

    <script>
        $(document).ready(function() {
    // Initialize an empty DataTable first
    var reportsTable = $('#reports-table').DataTable({
        "paging": true,
        "searching": true,
        "order": [[0, 'asc']], // Default sort on first column
        "data": [], // Start with no data
        "columns": [
            { "data": "program_name" },
            { "data": "section_name" },
            { "data": "academic_year" },
            { "data": "semester" },
            { "data": "year" },
            { "data": "reference_number" },
            { "data": "total_units" },
            { "data": "created_at" },
            { 
              "data": null,
              "render": function ( data, type, row ) {
                  return `<button class="btn" onclick="viewReport(${row.report_id})">View</button>`;
              }
            }
        ]
    });

    // Fetch and display the reports
    $.ajax({
        url: 'get_reports.php',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            console.log(data);
            reportsTable.clear().rows.add(data).draw(); // Add data to the table and draw it
        },
        error: function(xhr, status, error) {
            console.error("An error occurred while fetching reports:", error);
        }
    });
});


        // Function to view a single report and display it in the modal
        function viewReport(report_id) {
            $.ajax({
                url: 'view_report.php',
                method: 'GET',
                data: { id: report_id },
                success: function(response) {
                    var reportData = JSON.parse(response);  // Parse the JSON response

                    // Handle any errors returned from the server
                    if (reportData.error) {
                        alert(reportData.error);
                        return;
                    }

                    // Populate the modal with the report details
                    $('#reportContent').html(`
                        <h3>Generated Course Report</h3>
                        <p><strong>Program:</strong> ${reportData.program_name}</p>
                        <p><strong>Academic Year:</strong> ${reportData.academic_year}</p>
                        <p><strong>Semester:</strong> ${reportData.semester}</p>
                        <p><strong>Year:</strong> ${reportData.year}</p>
                        <p><strong>Total Units:</strong> ${reportData.total_units}</p>
                        <p><strong>Section:</strong> ${reportData.section_name}</p>
                    `);

                    // Clear the current list of courses
                    $('#courses-list').empty();

                    // Add each course to the list
                    if (reportData.courses && Array.isArray(reportData.courses)) {
                        reportData.courses.forEach(function(course) {
                            $('#courses-list').append(`
                                <tr>
                                    <td>${course.course_code}</td>
                                    <td>${course.course_name}</td>
                                    <td>${course.units}</td>
                                </tr>
                            `);
                        });
                    } else {
                        console.log('No courses found for this report');
                    }

                    // Show the modal
                    document.getElementById("reportModal").style.display = "block";
                },
                error: function(xhr, status, error) {
                    alert("An error occurred while fetching the report: " + error);
                }
            });
        }

        // Close modal when the close button is clicked
        document.querySelector(".close").onclick = function() {
            document.getElementById("reportModal").style.display = "none";
        }

        // Close modal when clicking outside the modal content
        window.onclick = function(event) {
            var modal = document.getElementById("reportModal");
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>
</html>
