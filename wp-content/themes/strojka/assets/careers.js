const careersDialog = document.querySelector('#careers-dialog');
if (careersDialog) {
  document.querySelectorAll('.careers-apply').forEach(button => {
    button.addEventListener('click', () => careersDialog.showModal());
  });
  careersDialog.querySelector('.careers-close').addEventListener('click', () => careersDialog.close());
  careersDialog.addEventListener('click', event => {
    if (event.target !== careersDialog) return;
    const rect = careersDialog.getBoundingClientRect();
    if (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom) careersDialog.close();
  });
}
