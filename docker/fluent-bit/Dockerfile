FROM debian:11-slim as busybox

RUN apt-get update \
	&& apt-get install -y --no-install-recommends busybox-static \
	&& rm -rf /var/lib/apt/lists/*

FROM fluent/fluent-bit:2.1

USER root
COPY --from=busybox /bin/busybox /bin/busybox
RUN ["/bin/busybox", "--install", "-s", "/bin"]
RUN ["/bin/sh", "-c", "echo 'fluent-bit:x:1000:' >> /etc/group && echo 'fluent-bit:x:1000:1000:Fluent Bit:/home/fluent-bit:/bin/false' >> /etc/passwd && mkdir -p /home/fluent-bit && chown 1000:1000 /home/fluent-bit"]
RUN ["/bin/sh", "-c", "mkdir -p /fluent-bit/state && chown -R 1000:1000 /fluent-bit"]

USER fluent-bit
