@push('scripts')
  <script>
    document.addEventListener('livewire:load', () => {
      Livewire.on('goToWizardStep', ({
        step
      }) => {
        const stepButton = [...document.querySelectorAll('[data-wizard-step]')].find(btn => btn.textContent
          .includes(step));
        if (stepButton) stepButton.click();
      });
    });
  </script>
@endpush
