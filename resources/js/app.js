import './bootstrap';

// DSUI: Test hot reload functionality
console.log('DSUI: Vite hot reload is working! 🚀');

// Simple interactive functionality
document.addEventListener('DOMContentLoaded', function() {
    console.log('DSUI: DOM loaded and ready');
    
    // Add click handler for any element with dsui-interactive class
    document.querySelectorAll('.dsui-interactive').forEach(element => {
        element.addEventListener('click', function() {
            console.log('DSUI: Interactive element clicked');
        });
    });
});
