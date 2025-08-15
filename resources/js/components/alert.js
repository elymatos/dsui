/**
 * DSUI Alert Component
 */

DS.component.alert = (config = {}) => ({
    visible: true,
    dismissible: config.dismissible || false,
    
    init() {
        if (config.autoHide) {
            setTimeout(() => {
                this.dismiss();
            }, config.autoHide);
        }
    },
    
    dismiss() {
        this.visible = false;
        this.$dispatch('alert-dismissed');
    }
});