import pyautogui
import time
import math
from datetime import datetime

# Definir la hora de inicio y fin
start_time = "08:23"  # Formato HH:MM ( 24 horas)
end_time = "19:40"  # Formato HH:MM (24 horas)


def wait_until_start(start_time):
    """Espera hasta la hora especificada antes de iniciar el programa."""
    while True:
        now = datetime.now().strftime("%H:%M")
        if now >= start_time:
            print(f"Iniciando a las {now}...")
            break
        time.sleep(180)  # Verifica cada 120 segundos


def move_in_circle(radius, steps, duration):
    """Mueve el mouse en un círculo."""
    center_x, center_y = pyautogui.position()

    for i in range(steps):
        angle = 2 * math.pi * i / steps
        x = center_x + radius * math.cos(angle)
        y = center_y + radius * math.sin(angle)
        pyautogui.moveTo(x, y, duration=duration)


def keep_awake(end_time):
    """Mantiene activo el mouse hasta la hora de finalización."""
    while True:
        now = datetime.now().strftime("%H:%M")
        if now >= end_time:
            print(f"Finalizando a las {now}.")
            break  # Sale del bucle cuando llega la hora de fin
        move_in_circle(radius=100, steps=50, duration=0.1)
        # Espera 95 segundos antes de mover el mouse nuevamente
        time.sleep(95)


if __name__ == "__main__":
    wait_until_start(start_time)
    keep_awake(end_time)
