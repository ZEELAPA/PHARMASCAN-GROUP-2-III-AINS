document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('archiveModal');
    const closeBtn = modal.querySelector('.close-btn');
    const tableBody = document.querySelector('.archive-list-view-container tbody');

    // Mappings for static fields
    const detailTable = document.getElementById('detailTable');
    const detailRecordId = document.getElementById('detailRecordId');
    const detailEmployeeName = document.getElementById('detailEmployeeName');
    const detailDate = document.getElementById('detailDate');
    const detailActionType = document.getElementById('detailActionType');
    const dynamicDetailsContainer = document.getElementById('dynamicDetailsContainer');

    // Function to handle the opening of the modal
    function openArchiveModal(row) {
        const detailsJson = row.getAttribute('data-details');
        
        // Set static summary fields
        detailTable.value = row.getAttribute('data-table-name');
        detailRecordId.value = row.getAttribute('data-record-id');
        detailEmployeeName.value = row.getAttribute('data-employee-name');
        detailDate.value = row.getAttribute('data-date');
        detailActionType.value = row.getAttribute('data-action-type');

        // Handle dynamic details based on JSON data
        populateDynamicDetails(detailsJson);

        modal.classList.add('active');
    }

    // Function to dynamically populate the specific details area
    function populateDynamicDetails(detailsJson) {
        dynamicDetailsContainer.innerHTML = ''; // Clear previous content

        try {
            const details = JSON.parse(detailsJson);
            
            for (const key in details) {
                if (details.hasOwnProperty(key)) {
                    const value = details[key];

                    const group = document.createElement('div');
                    group.classList.add('form-group');
                    
                    const label = document.createElement('label');
                    // Convert keys (like 'Scheduled Start') to readable labels
                    label.textContent = key; 
                    
                    const input = document.createElement('input');
                    input.type = 'text';
                    input.value = value;
                    input.readOnly = true;

                    group.appendChild(label);
                    group.appendChild(input);
                    dynamicDetailsContainer.appendChild(group);
                }
            }
            
        } catch (e) {
            dynamicDetailsContainer.innerHTML = '<p style="color: red;">Error loading specific archive details.</p>';
            console.error('Error parsing JSON details:', e);
        }
    }

    // Event Listener for Expand Button
    if (tableBody) {
        tableBody.addEventListener('click', (e) => {
            if (e.target.classList.contains('btn-expand')) {
                const row = e.target.closest('.archive-row');
                if (row) {
                    openArchiveModal(row);
                }
            }
        });
    }

    // Close Modal Functionality
    closeBtn.onclick = () => {
        modal.classList.remove('active');
    }

    window.onclick = (event) => {
        if (event.target === modal) {
            modal.classList.remove('active');
        }
    }

    // --- NEW LOGIC FOR EXPORT MODAL ---
    const exportModal = document.getElementById('exportConfigModal');
    const openExportBtn = document.getElementById('openExportModalBtn');
    const closeExportBtns = document.querySelectorAll('.close-export-btn');
    const exportForm = exportModal.querySelector('form');
    const formatSelector = document.getElementById('exportFormatSelector');

    if (openExportBtn && exportModal) {
        openExportBtn.addEventListener('click', () => {
            exportModal.classList.add('active');
        });

        closeExportBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                exportModal.classList.remove('active');
            });
        });

        // Close when clicking outside
        window.addEventListener('click', (e) => {
            if (e.target === exportModal) {
                exportModal.classList.remove('active');
            }
        });

        // Dynamically change the handler based on format selection
        if(formatSelector && exportForm) {
            formatSelector.addEventListener('change', function() {
                if(this.value === 'excel') {
                    exportForm.action = 'handlers/export-archives.php'; // Your Excel handler
                } else {
                    exportForm.action = 'handlers/export-archives-pdf.php'; // Your PDF handler
                }
            });
        }
    }
});