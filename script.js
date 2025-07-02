const loadingCircles = document.querySelectorAll('.loading-circle');

let colorIndex = 0;
const colors = ['#007bff', '#ff69b4', '#33cc33', '#6666cc'];

setInterval(() => {
  colorIndex = (colorIndex + 1) % colors.length;
  loadingCircles.forEach((circle) => {
    circle.style.backgroundColor = colors[colorIndex];
  });
}, 1000);

