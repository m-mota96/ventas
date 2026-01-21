import { ElNotification } from 'element-plus';

const showNotification = (message, type = 'success', duration = 6000) => {
    duration  = type === 'error' ? 10000 : 6000;
    let title = '';
    switch (type) {
        case 'warning':
            title = '¡Atención!';
            break;
        case 'error':
            title = '¡Error!';
            break;
        default:
            title = '¡Correcto!'
            break;
    }
    ElNotification({
        title,
        dangerouslyUseHTMLString: true,
        message,
        type,
        duration
    });
};

export default showNotification;