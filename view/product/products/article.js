
   (function () {
     const wrap   = document.querySelector('.price-range');
     if (!wrap) return;

     const minInp = wrap.querySelector('#price-min');
     const maxInp = wrap.querySelector('#price-max');
     const [lblMin, lblMax] = wrap.querySelectorAll('.labels span');

     // Si quieres exigir separación mínima, cambia GAP (p. ej. 5)
     const GAP = 0;

     const fmt = v => `£${Number(v).toFixed(0)}`;

     function sync(e) {
       let minV = parseFloat(minInp.value);
       let maxV = parseFloat(maxInp.value);

       // Evitar cruce
       if (e && e.target === minInp && minV > maxV - GAP) {
         minV = maxV - GAP;
         minInp.value = minV;
       } else if (e && e.target === maxInp && maxV < minV + GAP) {
         maxV = minV + GAP;
         maxInp.value = maxV;
       }

       // Actualizar etiquetas
       lblMin.textContent = fmt(minV);
       lblMax.textContent = fmt(maxV);
     }

     minInp.addEventListener('input', sync);
     maxInp.addEventListener('input', sync);
     // Inicializar
     sync();
   })();
   const colorInputs = document.querySelectorAll('.swatch input[name="color[]"]');
    colorInputs.forEach(i => i.addEventListener('change', () => {
      const selectedColors = [...colorInputs].filter(i => i.checked).map(i => i.value);
      console.log('Colores:', selectedColors); // úsalo para filtrar
    }));
