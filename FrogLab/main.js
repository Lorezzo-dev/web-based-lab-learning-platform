// JavaScript to handle pointer click and change the image
const image = document.getElementById('clickableImage');
const images = ['Sequence Images/Sequence 1.png', 'Sequence Images/Sequence 2.png', 'Sequence Images/Sequence 3.png']; // Add paths to the images
let currentIndex = 0; // Start from the first image (index 0)
let clickCount = 0; // Keep track of the number of clicks

image.addEventListener('click', () => {
    if (clickCount < 2) { // Limit to 3 clicks
        currentIndex = (currentIndex + 1) % images.length; // Loop through the images
        image.src = images[currentIndex]; // Update the image source
        clickCount++; // Increment the click count
    }
});



// Drag and Drop image Functionality
// Get the image element by its ID
const draggableImage = document.getElementById('dragImage');
        
let isDragging = false;
let offsetX, offsetY;

// Mouse down event to start dragging
draggableImage.addEventListener('mousedown', function(e) {
    isDragging = true;

    // Calculate the offset from the mouse position to the image's top-left corner
    offsetX = e.clientX - draggableImage.getBoundingClientRect().left;
    offsetY = e.clientY - draggableImage.getBoundingClientRect().top;

    // Change cursor to indicate dragging
    draggableImage.style.cursor = 'grabbing';
});

// Mouse move event to move the image
document.addEventListener('mousemove', function(e) {
    if (isDragging) {
        // Set the image's new position based on the mouse position and offsets
        draggableImage.style.left = `${e.clientX - offsetX}px`;
        draggableImage.style.top = `${e.clientY - offsetY}px`;
    }
});

// Mouse up event to stop dragging
document.addEventListener('mouseup', function() {
    isDragging = false;
    draggableImage.style.cursor = 'pointer'; // Reset cursor back to normal
});

//Drop Action Functionality