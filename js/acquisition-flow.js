// Use the data passed from PHP via wp_localize_script
var COMPANIESHOUSE_API_URL = acquisitionFlowData.COMPANIESHOUSE_API_URL;
var SALESFORCE_API_URL = acquisitionFlowData.SALESFORCE_API_URL;
var MAILCHIMP_API_URL = acquisitionFlowData.MAILCHIMP_API_URL;
var CALENDLY_URL = acquisitionFlowData.CALENDLY_URL;

var WPAQFL_BASE_SOLE = acquisitionFlowData.WPAQFL_BASE_SOLE;
var WPAQFL_BASE_PARTNERSHIP = acquisitionFlowData.WPAQFL_BASE_PARTNERSHIP;
var WPAQFL_BASE_LTD = acquisitionFlowData.WPAQFL_BASE_LTD;
var WPAQFL_BASE_LLP = acquisitionFlowData.WPAQFL_BASE_LLP;
var WPAQFL_PAYROLL_MATRIX = acquisitionFlowData.WPAQFL_PAYROLL_MATRIX;
var WPAQFL_VAT = acquisitionFlowData.WPAQFL_VAT;
var WPAQFL_SETUP = acquisitionFlowData.WPAQFL_SETUP;

document.addEventListener('DOMContentLoaded', function() {
    var flowRoot = document.getElementById('mazuma-flow-root');
    if (flowRoot) {
        // Get the main container to inject the flow root element
        var main = document.getElementById('Main');
        var footer = document.querySelector('.flexibleblocks-block.ctafooter');
        if (footer) {
            footer.remove();
        }
        // remove all other page elements
        var flexibleBlockContainer = document.querySelectorAll('.flexibleblocks');
        flexibleBlockContainer.forEach(function(el) {
            el.remove();
        })
        var breadcrumb = document.querySelector('.breadcrumb');
        if (breadcrumb) {
            breadcrumb.remove();
        }
        // inject to root into main#main
        if (flexibleBlockContainer) {
            
            if (flowRoot && main) {
                main.appendChild(flowRoot); // Insert at the end of the row
                flowRoot.style.display = "block";
            }
        }
    }
});

