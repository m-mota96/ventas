export const time12H = (
    date: string
) :string => {
    const fecha = new Date(date.replace(' ', 'T'));

    let horas = fecha.getHours();
    const minutos = fecha.getMinutes();
    const ampm = horas >= 12 ? 'PM' : 'AM';

    horas = horas % 12;
    horas = horas ? horas : 12;
    const hora = horas < 10 ? `0${horas}` : horas;

    const minutosFormateados = minutos < 10 ? '0' + minutos : minutos;

    return `${hora}:${minutosFormateados} ${ampm}`;
}